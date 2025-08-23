<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\RabItem;
use App\Models\DailyLog;
use App\Models\DailyReport; // TAMBAHKAN: Panggil model DailyReport
use Illuminate\Http\Request;
use Carbon\Carbon;

class PeriodicReportController extends Controller
{
    /**
     * GANTI: Logika utama pada fungsi index()
     * Fungsi ini sekarang akan menangani filter, mengambil data sumber daya, dan menghitung total.
     */
    public function index(Request $request, Package $package)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'filter' => 'nullable|string|in:all,this_period,until_now', // TAMBAHKAN: Validasi untuk filter
        ]);

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $filter = $request->input('filter', 'all'); // TAMBAHKAN: Ambil nilai filter

        // 1. Ambil semua item RAB
        $rabItems = RabItem::where('package_id', $package->id)->get()->keyBy('id');
        $rabTree = collect();

        if ($rabItems->isNotEmpty()) {
            // 2. Lampirkan progres
            $this->attachPeriodicProgress($rabItems, $package->id, $startDate, $endDate);
            
            // 3. Bangun pohon dan kalkulasi bobot
            $rabTree = $this->buildTree($rabItems);

            // TAMBAHKAN: Logika untuk menerapkan filter pada pohon yang sudah jadi
            if ($filter !== 'all') {
                $rabTree = $this->filterTree($rabTree, $filter);
            }
        }

        // TAMBAHKAN: Ambil data untuk kartu-kartu (Tim, Material, Alat, Cuaca)
        $reportsInPeriod = DailyReport::where('package_id', $package->id)
            ->whereBetween('report_date', [$startDate, $endDate])
            ->with(['personnel', 'weather', 'activities.materials.material', 'activities.equipment'])
            ->get();

        $allPersonnel = $reportsInPeriod->flatMap->personnel;
        $allMaterials = $reportsInPeriod->flatMap(function ($report) {
            return $report->activities->flatMap->materials;
        });
        $allEquipment = $reportsInPeriod->flatMap(function ($report) {
            return $report->activities->flatMap->equipment;
        });
        $allWeather = $reportsInPeriod->flatMap->weather;


        $viewData = [
            'package' => $package,
            'rabTree' => $rabTree,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filter' => $filter, // Kirim state filter ke view
            // Kirim data untuk kartu-kartu
            'allPersonnel' => $allPersonnel,
            'allMaterials' => $allMaterials,
            'allEquipment' => $allEquipment,
            'allWeather' => $allWeather,
        ];

        // Perbaiki Error Cetak: Pastikan data yang sama dikirim ke view 'print'
        if ($request->has('print')) {
            return view('periodic_reports.print', $viewData);
        }

        return view('periodic_reports.index', $viewData);
    }

    private function attachPeriodicProgress($rabItems, $packageId, $startDate, $endDate)
    {
        $logs = DailyLog::where('package_id', $packageId)
                        ->whereNotNull('rab_item_id')
                        ->whereBetween('log_date', [$startDate, $endDate])
                        ->get(['rab_item_id', 'progress_volume', 'log_date']);
        
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

    private function buildTree($items, $parentId = null)
    {
        $branch = collect();
        $childrenOfParent = $items->where('parent_id', $parentId)->sortBy('id');

        foreach ($childrenOfParent as $item) {
            $children = $this->buildTree($items, $item->id);
            $item->children = $children;

            if (is_null($item->volume)) {
                $item->weighting = $children->sum('weighting');
            }

            $item->bobot_lalu += $children->sum('bobot_lalu');
            $item->bobot_periode_ini += $children->sum('bobot_periode_ini');
            
            $branch->push($item);
        }
        return $branch;
    }

    /**
     * TAMBAHKAN: Fungsi baru untuk memfilter pohon berdasarkan progres.
     */
    private function filterTree($tree, $filter)
    {
        return $tree->map(function ($item) use ($filter) {
            // Jika item punya anak, filter anaknya terlebih dahulu
            if ($item->children->isNotEmpty()) {
                $item->children = $this->filterTree($item->children, $filter);
            }

            // Cek kondisi filter
            $sd_saat_ini = $item->bobot_lalu + $item->bobot_periode_ini;
            $periode_ini = $item->bobot_periode_ini;

            // Item akan tetap ditampilkan jika:
            // 1. Punya anak yang lolos filter.
            // 2. Atau, item itu sendiri lolos filter.
            if ($item->children->isNotEmpty()) {
                return $item;
            }
            if ($filter === 'this_period' && $periode_ini > 0) {
                return $item;
            }
            if ($filter === 'until_now' && $sd_saat_ini > 0) {
                return $item;
            }

            return null; // Item ini tidak lolos filter
        })->filter(); // Hapus item yang null (tidak lolos)
    }
}