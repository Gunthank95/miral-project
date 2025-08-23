<?php

namespace App\Services;

use App\Models\Package;
use App\Models\RabItem;
use App\Models\DailyLog;
use App\Models\DailyReport;
use Illuminate\Support\Collection;
use Carbon\Carbon; // Pastikan Carbon di-import

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

        // GANTI: Menggunakan metode yang lebih efisien untuk mengambil item RAB
        $relevantRabItems = $this->getRelevantRabItemsForDaily($report->activities, $packageId);
        $this->attachDailyProgress($relevantRabItems, $report->activities, $packageId, $report->report_date);
        
        return $this->buildTree($relevantRabItems);
    }

    /**
     * GANTI: Logika getRelevantRabItemsForDaily() dioptimalkan
     * Menghindari kueri di dalam loop (N+1 problem).
     */
    private function getRelevantRabItemsForDaily(Collection $activities, int $packageId): Collection
    {
        $reportedItemIds = $activities->pluck('rab_item_id')->filter()->unique();
        if ($reportedItemIds->isEmpty()) return collect();

        // Menggunakan relasi 'ancestors' untuk mengambil semua induk dalam satu kueri efisien.
        $reportedItems = RabItem::with('ancestors')->whereIn('id', $reportedItemIds)->get();

        $allRelevantIds = $reportedItems->flatMap(function ($item) {
            return $item->ancestors->pluck('id');
        })->merge($reportedItemIds)->unique();

        return RabItem::whereIn('id', $allRelevantIds)->get()->keyBy('id');
    }

    /**
     * GANTI: Logika attachDailyProgress() dioptimalkan
     * Mengambil semua data log sekaligus sebelum loop.
     */
    private function attachDailyProgress(Collection $rabItems, Collection $activities, int $packageId, string $selectedDate): void
    {
        $rabItemIds = $rabItems->pluck('id');
        
        // Ambil semua log progres sebelumnya dalam satu kueri
        $previousLogs = DailyLog::where('package_id', $packageId)
                                ->whereIn('rab_item_id', $rabItemIds)
                                ->whereDate('log_date', '<', $selectedDate)
                                ->select('rab_item_id', \DB::raw('SUM(progress_volume) as total_volume'))
                                ->groupBy('rab_item_id')
                                ->pluck('total_volume', 'rab_item_id');

        foreach ($rabItems as $item) {
            $activity = $activities->firstWhere('rab_item_id', $item->id);
            
            $item->is_reported_activity = (bool)$activity;
            $item->progress_volume_period = $activity->progress_volume ?? 0;
            // Ambil data dari koleksi yang sudah kita siapkan, bukan kueri baru
            $item->previous_progress_volume = $previousLogs->get($item->id, 0); 
            
            $item->progress_weight_period = 0;
            $item->previous_progress_weight = 0;

            if ($item->volume > 0) {
                $item->progress_weight_period = ($item->progress_volume_period / $item->volume) * $item->weighting;
                $item->previous_progress_weight = ($item->previous_progress_volume / $item->volume) * $item->weighting;
            }
        }
    }

    /**
     * ========================================================================
     * LOGIKA UNTUK LAPORAN PERIODIK (PERIODIC REPORT)
     * ========================================================================
     */

    /**
     * GANTI: Kueri di generatePeriodicReport() dioptimalkan
     * Menggunakan eager loading yang lebih dalam untuk data sumber daya.
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

        // GANTI: Kueri ini diperbaiki dengan eager loading yang lebih spesifik
        $reportsInPeriod = DailyReport::where('package_id', $package->id)
            ->whereBetween('report_date', [$startDate, $endDate])
            ->with([
                'personnel', 
                'weather', 
                'activities.materials.material', // Eager load material di dalam materials
                'activities.equipment'
            ])
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