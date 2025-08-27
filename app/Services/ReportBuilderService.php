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
     * LOGIKA UNTUK LAPORAN PERIODIK (PERIODIC REPORT)
     * ========================================================================
     */
    public function generatePeriodicReport(Package $package, Carbon $startDate, Carbon $endDate, string $filter = 'all'): Collection
    {
        // 1. Ambil semua item RAB dan semua log aktivitas yang relevan
        $rabItems = RabItem::where('package_id', $package->id)->get();
        $allLogs = DailyLog::where('package_id', $package->id)
                            ->whereHas('report', function ($query) use ($startDate, $endDate) {
                                $query->whereBetween('report_date', [$startDate, $endDate]);
                            })
                            ->get();

        // 2. Lampirkan data progres periodik ke setiap item RAB
        // PERBAIKAN: Mengirim $allLogs bukan $package->id
        $this->attachPeriodicProgress($rabItems, $allLogs, $startDate, $endDate);
        
        // 3. Bangun struktur pohon dari item RAB yang sudah diolah
        $rabTree = $this->buildTree($rabItems);

        // 4. Filter pohon RAB berdasarkan kriteria yang dipilih
        $filteredRabTree = $this->filterRabTree($rabTree, $filter);

        // 5. Kumpulkan semua data pendukung dari laporan yang relevan
        $reportIds = DailyReport::where('package_id', $package->id)
            ->whereBetween('report_date', [$startDate, $endDate])
            ->pluck('id');
            
        $allPersonnel = DailyReportPersonnel::whereIn('daily_report_id', $reportIds)->get();
        $allWeather = DailyReportWeather::whereIn('daily_report_id', $reportIds)->orderBy('time')->get();

        $relevantLogIds = $this->getRelevantLogIds($filteredRabTree);
        $allMaterials = DailyLogMaterial::whereIn('daily_log_id', $relevantLogIds)->with('material')->get();
        $allEquipment = DailyLogEquipment::whereIn('daily_log_id', $relevantLogIds)->get();

        // 6. Kembalikan data yang sudah difilter dengan benar
        return collect([
            'rabTree' => $filteredRabTree,
            'allPersonnel' => $allPersonnel,
            'allMaterials' => $allMaterials,
            'allEquipment' => $allEquipment,
            'allWeather' => $allWeather,
        ]);
    }

    /**
     * PERBAIKAN: Definisi fungsi diubah untuk menerima Collection $allLogs, bukan int $packageId
     */
    private function attachPeriodicProgress(Collection $rabItems, Collection $allLogs, Carbon $startDate, Carbon $endDate): void
    {
        $allLogsGrouped = $allLogs->groupBy('rab_item_id');

        foreach ($rabItems as $item) {
            $logsForItem = $allLogsGrouped->get($item->id, collect());

            // Volume s/d Periode Lalu
            $item->volume_lalu = $logsForItem->filter(function ($log) use ($startDate) {
                return Carbon::parse($log->report->report_date)->lt($startDate);
            })->sum('progress_volume');

            // Volume Periode Ini
            $item->volume_periode_ini = $logsForItem->filter(function ($log) use ($startDate, $endDate) {
                return Carbon::parse($log->report->report_date)->between($startDate, $endDate);
            })->sum('progress_volume');

            // Hitung Bobot
            if ($item->volume > 0) {
                $item->bobot_lalu = ($item->volume_lalu / $item->volume) * $item->weighting;
                $item->bobot_periode_ini = ($item->volume_periode_ini / $item->volume) * $item->weighting;
            } else {
                $item->bobot_lalu = 0;
                $item->bobot_periode_ini = 0;
            }
        }
    }

    /**
     * ========================================================================
     * LOGIKA BERSAMA (SHARED LOGIC) - TIDAK ADA PERUBAHAN DI BAWAH INI
     * ========================================================================
     */
    private function buildTree(Collection $items, $parentId = null): Collection
    {
        $branch = collect();
        $childrenOfParent = $items->where('parent_id', $parentId)->sortBy('id');

        foreach ($childrenOfParent as $item) {
            $children = $this->buildTree($items, $item->id);
            $item->children = $children->isNotEmpty() ? $children : collect();
            
            if (is_null($item->volume)) {
                $item->weighting = $children->sum('weighting');
            }

            if (isset($item->bobot_lalu)) {
                $item->bobot_lalu += $children->sum('bobot_lalu');
                $item->bobot_periode_ini += $children->sum('bobot_periode_ini');
            }
            if (isset($item->previous_progress_weight)) {
                $item->previous_progress_weight += $children->sum('previous_progress_weight');
                $item->progress_weight_period += $children->sum('progress_weight_period');
            }
            if (isset($item->volume_lalu)) {
                $item->volume_lalu += $children->sum('volume_lalu');
                $item->volume_periode_ini += $children->sum('volume_periode_ini');
            }

            $branch->push($item);
        }
        return $branch;
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
            return true; // Untuk filter 'all'
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
}