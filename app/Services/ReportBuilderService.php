<?php

namespace App\Services;

use App\Models\Package;
use App\Models\RabItem;
use App\Models\DailyLog;
use App\Models\DailyReport;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ReportBuilderService
{
    /**
     * ========================================================================
     * LOGIKA UNTUK LAPORAN HARIAN (DAILY REPORT)
     * ========================================================================
     */

    public function generateDailyReport(DailyReport $report, int $packageId, string $filter = 'this_period'): Collection
    {
        if ($report->activities->isEmpty()) {
            return collect();
        }

        $relevantRabItems = $this->getRelevantRabItemsForDaily($report->activities, $packageId);
        $this->attachDailyProgress($relevantRabItems, $report->activities, $packageId, $report->report_date);
        
        $tree = $this->buildTree($relevantRabItems);

        // Jika filter bukan 'all_items', jalankan fungsi filter rekursif
        if ($filter !== 'all_items') {
            return $this->filterTreeDaily($tree, $filter);
        }

        // Jika 'all_items', kembalikan semua
        return $tree;
    }

    /**
     * FUNGSI BARU: Filter rekursif untuk menyaring pohon aktivitas harian.
     */
    private function filterTreeDaily(Collection $nodes, string $filter): Collection
    {
        return $nodes->map(function ($node) use ($filter) {
            // 1. Filter terlebih dahulu anak-anak dari node ini secara rekursif
            if ($node->children->isNotEmpty()) {
                $node->children = $this->filterTreeDaily($node->children, $filter);
            }

            // 2. Tentukan apakah node ini harus disimpan berdasarkan progresnya SENDIRI
            $shouldKeep = false;
            if ($filter === 'this_period') {
                if ($node->progress_weight_period > 0) {
                    $shouldKeep = true;
                }
            } elseif ($filter === 'until_now') {
                if (($node->previous_progress_weight + $node->progress_weight_period) > 0) {
                    $shouldKeep = true;
                }
            }

            // 3. Jika node ini tidak punya progres, cek apakah ia masih punya anak (setelah difilter).
            //    Jika ya, simpan juga node ini (sebagai induk).
            if (!$shouldKeep && $node->children->isNotEmpty()) {
                $shouldKeep = true;
            }

            return $shouldKeep ? $node : null;
        })->filter(); // Hapus semua node yang bernilai null
    }



    private function getRelevantRabItemsForDaily(Collection $activities, int $packageId): Collection
    {
        $reportedItemIds = $activities->pluck('rab_item_id')->filter()->unique();
        if ($reportedItemIds->isEmpty()) {
            return collect();
        }
        
        $allPackageItems = RabItem::where('package_id', $packageId)->get();
        $lineageIds = collect();

        foreach ($reportedItemIds as $itemId) {
            $item = $allPackageItems->find($itemId);
            if ($item) {
                // Panggil getAncestorIds yang sudah diperbaiki
                $lineageIds = $lineageIds->merge($this->getAncestorIds($item, $allPackageItems));
            }
        }

        $finalIds = $reportedItemIds->merge($lineageIds)->unique();
        return $allPackageItems->whereIn('id', $finalIds);
    }

    /**
     * PERBAIKAN: Fungsi ini sekarang mengembalikan ID (angka), bukan Objek.
     */
    private function getAncestorIds(RabItem $item, Collection $allItems): Collection
    {
        $ancestorIds = collect();
        $parent = $item->parent_id ? $allItems->find($item->parent_id) : null;
        while ($parent) {
            $ancestorIds->push($parent->id); // <-- PUSH ID-NYA, BUKAN OBJEKNYA
            $parent = $parent->parent_id ? $allItems->find($parent->parent_id) : null;
        }
        return $ancestorIds;
    }

    private function attachDailyProgress(Collection $rabItems, Collection $activities, int $packageId, Carbon $reportDate): void
    {
        $rabItemIds = $rabItems->pluck('id');
        
        $previousProgress = DailyLog::where('package_id', $packageId)
            ->whereIn('rab_item_id', $rabItemIds)
            ->whereDate('log_date', '<', $reportDate)
            ->groupBy('rab_item_id')
            ->selectRaw('rab_item_id, SUM(progress_volume) as total_volume')
            ->pluck('total_volume', 'rab_item_id');

        $periodProgress = $activities->groupBy('rab_item_id')
            ->map(fn($logs) => $logs->sum('progress_volume'));

        foreach ($rabItems as $item) {
            $item->is_reported_activity = $activities->contains('rab_item_id', $item->id);
            $previousVolume = $previousProgress->get($item->id, 0);
            $periodVolume = $periodProgress->get($item->id, 0);
            $item->previous_progress_volume = $previousVolume;
            $item->progress_volume_period = $periodVolume;
            
            if ($item->volume > 0) {
                $item->previous_progress_weight = ($previousVolume / $item->volume) * $item->weighting;
                $item->progress_weight_period = ($periodVolume / $item->volume) * $item->weighting;
            } else {
                $item->previous_progress_weight = 0;
                $item->progress_weight_period = 0;
            }
        }
    }


    /**
     * ========================================================================
     * LOGIKA UNTUK LAPORAN PERIODIK (PERIODIC REPORT)
     * ========================================================================
     */
    
    public function generatePeriodicReport(Package $package, Carbon $startDate, Carbon $endDate, string $filter): Collection
    {
        $rabItems = RabItem::where('package_id', $package->id)->get();
        if ($rabItems->isEmpty()) return collect();
        $this->attachPeriodicProgress($rabItems, $package->id, $startDate, $endDate);
        $tree = $this->buildTree($rabItems);

        if ($filter !== 'all_items') {
            return $this->filterTreePeriodic($tree, $filter);
        }
        return $tree;
    }
    
    private function filterTreePeriodic(Collection $nodes, string $filter): Collection
    {
        return $nodes->map(function ($node) use ($filter) {
            if ($node->children->isNotEmpty()) {
                $node->children = $this->filterTreePeriodic($node->children, $filter);
            }
            $shouldKeep = false;
            if ($filter === 'this_period' && $node->bobot_periode_ini > 0) $shouldKeep = true;
            if ($filter === 'until_now' && ($node->bobot_lalu + $node->bobot_periode_ini) > 0) $shouldKeep = true;
            if (!$shouldKeep && $node->children->isNotEmpty()) $shouldKeep = true;
            return $shouldKeep ? $node : null;
        })->filter();
    }
	
    private function attachPeriodicProgress(Collection $rabItems, int $packageId, Carbon $startDate, Carbon $endDate): void
    {
        $rabItemIds = $rabItems->pluck('id');

        $bobotLalu = DailyLog::where('package_id', $packageId)
            ->whereIn('rab_item_id', $rabItemIds)
            ->where('log_date', '<', $startDate)
            ->groupBy('rab_item_id')
            ->selectRaw('rab_item_id, SUM(progress_volume) as total_volume')
            ->pluck('total_volume', 'rab_item_id');

        $bobotPeriodeIni = DailyLog::where('package_id', $packageId)
            ->whereIn('rab_item_id', $rabItemIds)
            ->whereBetween('log_date', [$startDate, $endDate])
            ->groupBy('rab_item_id')
            ->selectRaw('rab_item_id, SUM(progress_volume) as total_volume')
            ->pluck('total_volume', 'rab_item_id');

        foreach ($rabItems as $item) {
            $volumeLalu = $bobotLalu->get($item->id, 0);
            $volumePeriodeIni = $bobotPeriodeIni->get($item->id, 0);

            $item->volume_lalu = $volumeLalu;
            $item->volume_periode_ini = $volumePeriodeIni;

            if ($item->volume > 0) {
                $item->bobot_lalu = ($volumeLalu / $item->volume) * $item->weighting;
                $item->bobot_periode_ini = ($volumePeriodeIni / $item->volume) * $item->weighting;
            } else {
                $item->bobot_lalu = 0;
                $item->bobot_periode_ini = 0;
            }
        }
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

            if (isset($item->bobot_lalu)) {
                $item->bobot_lalu += $children->sum('bobot_lalu');
                $item->bobot_periode_ini += $children->sum('bobot_periode_ini');
            }
            if (isset($item->previous_progress_weight)) {
                $item->previous_progress_weight += $children->sum('previous_progress_weight');
                $item->progress_weight_period += $children->sum('progress_weight_period');
            }
            
            $branch->push($item);
        }
        
        return $branch;
    }
}