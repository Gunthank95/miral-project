<?php

namespace App\Services;

use App\Models\DailyLog;
use App\Models\DailyLogEquipment;
use App\Models\DailyLogMaterial;
use App\Models\DailyReport;
use App\Models\DailyReportPersonnel;
use App\Models\DailyReportWeather;
use App\Models\Package;
use App\Models\RabItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportBuilderService
{
    /**
     * ========================================================================
     * FUNGSI UNTUK LAPORAN HARIAN (DAILY REPORT)
     * ========================================================================
     */
    public function generateDailyReport(DailyReport $report, int $packageId, string $filter = 'this_period'): Collection
	{
		if ($report->activities->isEmpty()) {
			return collect();
		}

		// Langkah 1: Ambil SEMUA item RAB dari database untuk paket ini.
		// Ini adalah kunci agar struktur dan bobot kontrak induknya sama persis seperti di halaman RAB.
		$allItems = RabItem::where('package_id', $packageId)->get()->keyBy('id');

		// Langkah 2: Inisialisasi properti progres untuk semua item agar tidak error.
		foreach ($allItems as $item) {
			$item->previous_progress_weight = 0;
			$item->progress_weight_period = 0;
			$item->previous_progress_volume = 0;
			$item->progress_volume_period = 0;
		}

		// Langkah 3: Tempelkan data progres dari log harian HANYA ke item-item yang relevan.
		$this->attachDailyProgress($allItems, $report->activities, $packageId, $report->report_date);
		
		// Langkah 4: Bangun pohon struktur LENGKAP.
		// Di dalam fungsi buildTree, bobot kontrak induk akan dihitung dengan benar
		// karena sekarang ia memiliki semua data anak-anaknya.
		$tree = $this->buildTree($allItems);

		// Langkah 5: Setelah pohon lengkap dengan bobot yang benar, baru kita filter
		// untuk menampilkan hanya item yang punya progres (sesuai logika filter).
		if ($filter !== 'all_items') {
			return $this->filterTreeDaily($tree, $filter);
		}

		return $tree;
	}

    private function getRelevantRabItemsForDaily(Collection $activities, int $packageId): Collection
    {
        $activityRabIds = $activities->pluck('rab_item_id')->filter()->unique();
        $rabItemsWithActivities = RabItem::whereIn('id', $activityRabIds)->get();
        $allRabItems = RabItem::where('package_id', $packageId)->get();

        $relevantItems = $rabItemsWithActivities;
        foreach ($rabItemsWithActivities as $item) {
            $parents = $this->getParents($item, $allRabItems);
            $relevantItems = $relevantItems->merge($parents);
        }

        return $relevantItems->unique('id');
    }
    
    private function attachDailyProgress(Collection $rabItems, Collection $activities, int $packageId, $reportDate): void
    {
        $allLogsUntilToday = DailyLog::where('package_id', $packageId)
            ->whereHas('report', function ($q) use ($reportDate) {
                $q->where('report_date', '<=', $reportDate);
            })->get();

        foreach ($rabItems as $item) {
            // Progres Periode Ini (Hari Ini)
            $item->progress_volume_period = $activities->where('rab_item_id', $item->id)->sum('progress_volume');

            // Progres Sebelumnya
            $item->previous_progress_volume = $allLogsUntilToday
                ->where('rab_item_id', $item->id)
                ->filter(function ($log) use ($reportDate) {
                    return Carbon::parse($log->report->report_date)->lt($reportDate);
                })
                ->sum('progress_volume');
            
            if ($item->volume > 0) {
                $item->previous_progress_weight = ($item->previous_progress_volume / $item->volume) * $item->weighting;
                $item->progress_weight_period = ($item->progress_volume_period / $item->volume) * $item->weighting;
            } else {
                $item->previous_progress_weight = 0;
                $item->progress_weight_period = 0;
            }
        }
    }
	
    private function filterTreeDaily(Collection $tree, string $filter): Collection
    {
        return $tree->filter(function ($item) use ($filter) {
            if ($item->children->isNotEmpty()) {
                $item->children = $this->filterTreeDaily($item->children, $filter);
            }

            if ($filter === 'this_period') {
                return $item->progress_volume_period > 0 || $item->children->isNotEmpty();
            }
            if ($filter === 'until_now') {
                return ($item->previous_progress_volume + $item->progress_volume_period) > 0 || $item->children->isNotEmpty();
            }
            return true;
        })->values();
    }

    /**
     * ========================================================================
     * FUNGSI UNTUK LAPORAN PERIODIK (PERIODIC REPORT)
     * ========================================================================
     */
    public function generatePeriodicReport(Package $package, Carbon $startDate, Carbon $endDate, string $filter = 'all'): Collection
    {
        $rabItems = RabItem::where('package_id', $package->id)->get();

        $allLogs = DailyLog::where('package_id', $package->id)
                            ->whereHas('report', function ($query) use ($endDate) {
                                $query->where('report_date', '<=', $endDate);
                            })
                            ->with('report')
                            ->get();

        $this->attachPeriodicProgress($rabItems, $allLogs, $startDate, $endDate);
        
        $rabTree = $this->buildTree($rabItems);
        $filteredRabTree = $this->filterRabTree($rabTree, $filter);

        $reportIds = DailyReport::where('package_id', $package->id)
            ->whereBetween('report_date', [$startDate, $endDate])
            ->pluck('id');
            
        $allPersonnel = $reportIds->isNotEmpty() ? DailyReportPersonnel::whereIn('daily_report_id', $reportIds)->get() : collect();
        $allWeather = $reportIds->isNotEmpty() ? DailyReportWeather::whereIn('daily_report_id', $reportIds)->orderBy('time')->get() : collect();

        $relevantLogIds = $this->getRelevantLogIds($filteredRabTree);
        $allMaterials = !empty($relevantLogIds) ? DailyLogMaterial::whereIn('daily_log_id', $relevantLogIds)->with('material')->get() : collect();
        $allEquipment = !empty($relevantLogIds) ? DailyLogEquipment::whereIn('daily_log_id', $relevantLogIds)->get() : collect();

        return collect([
            'rabTree' => $filteredRabTree,
            'allPersonnel' => $allPersonnel,
            'allMaterials' => $allMaterials,
            'allEquipment' => $allEquipment,
            'allWeather' => $allWeather,
        ]);
    }

    private function attachPeriodicProgress(Collection $rabItems, Collection $allLogs, Carbon $startDate, Carbon $endDate): void
    {
        $allLogsGrouped = $allLogs->groupBy('rab_item_id');

        foreach ($rabItems as $item) {
            $logsForItem = $allLogsGrouped->get($item->id, collect());

            $item->volume_lalu = $logsForItem->filter(function ($log) use ($startDate) {
                return Carbon::parse($log->report->report_date)->lt($startDate);
            })->sum('progress_volume');

            $item->volume_periode_ini = $logsForItem->filter(function ($log) use ($startDate, $endDate) {
                return Carbon::parse($log->report->report_date)->between($startDate, $endDate);
            })->sum('progress_volume');
            
            // Simpan ID log periode ini untuk mengambil material/alat nanti
            $item->log_ids_period = $logsForItem->filter(function ($log) use ($startDate, $endDate) {
                return Carbon::parse($log->report->report_date)->between($startDate, $endDate);
            })->pluck('id')->toArray();

            if ($item->volume > 0) {
                $item->bobot_lalu = ($item->volume_lalu / $item->volume) * $item->weighting;
                $item->bobot_periode_ini = ($item->volume_periode_ini / $item->volume) * $item->weighting;
            } else {
                $item->bobot_lalu = 0;
                $item->bobot_periode_ini = 0;
            }
        }
    }

    private function filterRabTree(Collection $tree, string $filter): Collection
    {
        return $tree->filter(function ($item) use ($filter) {
            if ($item->children->isNotEmpty()) {
                $item->children = $this->filterRabTree($item->children, $filter);
            }

            if ($filter === 'this_period') {
                return $item->bobot_periode_ini > 0 || $item->children->isNotEmpty();
            }
            if ($filter === 'until_now') {
                return ($item->bobot_lalu + $item->bobot_periode_ini) > 0 || $item->children->isNotEmpty();
            }
            return true;
        })->values();
    }
    
    private function getRelevantLogIds(Collection $tree): array
    {
        $logIds = [];
        foreach ($tree as $item) {
            if (isset($item->log_ids_period)) {
                $logIds = array_merge($logIds, $item->log_ids_period);
            }
            if ($item->children->isNotEmpty()) {
                $logIds = array_merge($logIds, $this->getRelevantLogIds($item->children));
            }
        }
        return array_unique($logIds);
    }

    /**
     * ========================================================================
     * LOGIKA BERSAMA (SHARED LOGIC)
     * ========================================================================
     */
    private function buildTree(Collection $items, $parentId = null): Collection
    {
        $branch = collect();
        $childrenOfParent = $items->where('parent_id', $parentId)->sortBy('id');

        foreach ($childrenOfParent as $item) {
            $children = $this->buildTree($items, $item->id);
            if ($children->isNotEmpty()) {
                $item->children = $children;
            } else {
                $item->children = collect();
            }
			
			if (is_null($item->volume)) {
				$item->weighting = $children->sum('weighting');
			}
            
            if (is_null($item->volume)) {
                $item->weighting = $children->sum('weighting');
            }

            // Akumulasi progres dari anak ke induk
            if (isset($children[0]->previous_progress_weight)) { // Cek apakah ini laporan harian
                 $item->previous_progress_weight += $children->sum('previous_progress_weight');
                 $item->progress_weight_period += $children->sum('progress_weight_period');
                 $item->previous_progress_volume += $children->sum('previous_progress_volume');
                 $item->progress_volume_period += $children->sum('progress_volume_period');
            }
            
            if (isset($children[0]->bobot_lalu)) { // Cek apakah ini laporan periodik
                $item->bobot_lalu += $children->sum('bobot_lalu');
                $item->bobot_periode_ini += $children->sum('bobot_periode_ini');
            }
			
			$item->item_progress = ($item->volume > 0) ? (($item->previous_progress_volume + $item->progress_volume_period) / $item->volume) * 100 : 0;

            $branch->push($item);
        }
        return $branch;
    }

    private function getParents($item, $allItems)
    {
        $parents = collect();
        $parent = $allItems->where('id', $item->parent_id)->first();
        while ($parent) {
            $parents->push($parent);
            $parent = $allItems->where('id', $parent->parent_id)->first();
        }
        return $parents;
    }
}