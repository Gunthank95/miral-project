<?php

namespace App\Services;

use App\Models\Package;
use App\Models\RabItem;
use App\Models\DailyLog;
use App\Models\DailyReport;
use Illuminate\Support\Collection;

class ReportBuilderService
{
    /**
     * ========================================================================
     * LOGIKA UNTUK LAPORAN HARIAN (DAILY REPORT)
     * ========================================================================
     */

    public function generateDailyReport(DailyReport $report, int $packageId): Collection
    {
        if ($report->activities->isEmpty()) {
            return collect();
        }

        $relevantRabItems = $this->getRelevantRabItemsForDaily($report->activities, $packageId);
        $this->attachDailyProgress($relevantRabItems, $report->activities, $packageId, $report->report_date);
        
        return $this->buildTree($relevantRabItems);
    }

    private function getRelevantRabItemsForDaily(Collection $activities, int $packageId): Collection
    {
        $reportedItemIds = $activities->pluck('rab_item_id')->filter()->unique();
        if ($reportedItemIds->isEmpty()) {
            return collect();
        }

        $topLevelParentIds = collect();
        foreach ($reportedItemIds as $id) {
            $item = RabItem::find($id);
            while ($item && $item->parent_id) {
                $item = $item->parent;
            }
            if ($item) {
                $topLevelParentIds->push($item->id);
            }
        }

        $allRelevantIds = collect();
        $itemsToProcess = $topLevelParentIds->unique();

        while ($itemsToProcess->isNotEmpty()) {
            $currentIds = $itemsToProcess->all();
            $allRelevantIds = $allRelevantIds->merge($currentIds);
            
            $childIds = RabItem::whereIn('parent_id', $currentIds)->pluck('id');
            $itemsToProcess = $childIds;
        }

        return RabItem::whereIn('id', $allRelevantIds->unique())->get()->keyBy('id');
    }

    private function attachDailyProgress(Collection $rabItems, Collection $activities, int $packageId, string $selectedDate): void
    {
        foreach ($rabItems as $item) {
            $activity = $activities->firstWhere('rab_item_id', $item->id);
            
            $item->is_reported_activity = false;
            $item->progress_volume_period = 0;
            $item->previous_progress_volume = 0;
            $item->progress_weight_period = 0;
            $item->previous_progress_weight = 0;

            if ($activity) {
                $previousVolume = DailyLog::where('package_id', $packageId)
                                          ->where('rab_item_id', $item->id)
                                          ->whereDate('log_date', '<', $selectedDate)
                                          ->sum('progress_volume');

                $item->is_reported_activity = true;
                $item->progress_volume_period = $activity->progress_volume ?? 0;
                $item->previous_progress_volume = $previousVolume;
                
                if ($item->volume > 0) {
                    $item->progress_weight_period = ($item->progress_volume_period / $item->volume) * $item->weighting;
                    $item->previous_progress_weight = ($item->previous_progress_volume / $item->volume) * $item->weighting;
                }
            }
        }
    }

    /**
     * ========================================================================
     * LOGIKA UNTUK LAPORAN PERIODIK (PERIODIC REPORT)
     * ========================================================================
     */

    public function generatePeriodicReport(Package $package, string $startDate, string $endDate, string $filter = 'all'): array
    {
        $rabItems = RabItem::where('package_id', $package->id)->get()->keyBy('id');
        $rabTree = collect();

        if ($rabItems->isNotEmpty()) {
            $this->attachPeriodicProgress($rabItems, $package->id, $startDate, $endDate);
            $rabTree = $this->buildTree($rabItems);

            if ($filter !== 'all') {
                $rabTree = $this->filterTree($rabTree, $filter);
            }
        }

        $reportsInPeriod = DailyReport::where('package_id', $package->id)
            ->whereBetween('report_date', [$startDate, $endDate])
            ->with(['personnel', 'weather', 'activities.materials.material', 'activities.equipment'])
            ->get();
        
        return [
            'rabTree' => $rabTree,
            'allPersonnel' => $reportsInPeriod->flatMap->personnel,
            'allMaterials' => $reportsInPeriod->flatMap(fn($r) => $r->activities->flatMap->materials),
            'allEquipment' => $reportsInPeriod->flatMap(fn($r) => $r->activities->flatMap->equipment),
            'allWeather' => $reportsInPeriod->flatMap->weather,
        ];
    }
    
    private function attachPeriodicProgress(Collection $rabItems, int $packageId, string $startDate, string $endDate): void
    {
        $logs = DailyLog::where('package_id', $packageId)
                        ->whereNotNull('rab_item_id')
                        ->whereBetween('log_date', [$startDate, $endDate])
                        ->get(['rab_item_id', 'progress_volume']);
        
        $logsBefore = DailyLog::where('package_id', $packageId)
                              ->whereNotNull('rab_item_id')
                              ->where('log_date', '<', $startDate)
                              ->get(['rab_item_id', 'progress_volume']);

        foreach ($rabItems as $item) {
            $item->volume_lalu = $logsBefore->where('rab_item_id', $item->id)->sum('progress_volume');
            $item->volume_periode_ini = $logs->where('rab_item_id', $item->id)->sum('progress_volume');
            $item->bobot_lalu = 0;
            $item->bobot_periode_ini = 0;

            if ($item->volume > 0) {
                $item->bobot_lalu = ($item->volume_lalu / $item->volume) * $item->weighting;
                $item->bobot_periode_ini = ($item->volume_periode_ini / $item->volume) * $item->weighting;
            }
        }
    }

    private function filterTree(Collection $tree, string $filter): Collection
    {
        return $tree->map(function ($item) use ($filter) {
            if ($item->children->isNotEmpty()) {
                $item->children = $this->filterTree($item->children, $filter);
            }

            $sd_saat_ini = $item->bobot_lalu + $item->bobot_periode_ini;
            $periode_ini = $item->bobot_periode_ini;

            if ($item->children->isNotEmpty()) return $item;
            if ($filter === 'this_period' && $periode_ini > 0) return $item;
            if ($filter === 'until_now' && $sd_saat_ini > 0) return $item;

            return null;
        })->filter();
    }


    /**
     * ========================================================================
     * FUNGSI BERSAMA (SHARED FUNCTION)
     * ========================================================================
     */

    private function buildTree(Collection $items, $parentId = null): Collection
    {
        $branch = collect();
        $childrenOfParent = $items->where('parent_id', $parentId)->sortBy('id');

        foreach ($childrenOfParent as $item) {
            $children = $this->buildTree($items, $item->id);
            $item->children = $children;
            
            if (is_null($item->volume)) {
                $item->weighting = $children->sum('weighting');
            }

            // Akumulasi progres (untuk kedua jenis laporan)
            if (isset($item->bobot_lalu)) { // Periodic
                $item->bobot_lalu += $children->sum('bobot_lalu');
                $item->bobot_periode_ini += $children->sum('bobot_periode_ini');
            }
            if (isset($item->previous_progress_weight)) { // Daily
                $item->previous_progress_weight += $children->sum('previous_progress_weight');
                $item->progress_weight_period += $children->sum('progress_weight_period');
            }
            
            $branch->push($item);
        }

        // Filter akhir untuk Laporan Harian (hanya tampilkan yang relevan)
        if (isset($branch->first()->is_reported_activity)) {
            return $branch->filter(function ($item) {
                 return ($item->previous_progress_weight + $item->progress_weight_period) > 0 || $item->is_reported_activity;
            });
        }
        
        return $branch;
    }
}