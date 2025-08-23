<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\DailyReport;
use App\Models\DailyLog;
use App\Models\RabItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DailyReportController extends Controller
{
    public function index(Request $request, Package $package)
    {
        $selectedDate = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();

        $report = $package->dailyReports()
                          ->whereDate('report_date', $selectedDate)
                          ->with([
                              'weather', 
                              'personnel', 
                              'activities.rabItem', 
                              'activities.materials.material',
                              'activities.equipment',
                              'activities.photos'
                          ])
                          ->first();

        $activityTree = collect();

        if ($report && $report->activities->isNotEmpty()) {
            // Langkah 1: Ambil SEMUA item RAB yang relevan (termasuk yang tidak dilaporkan)
            $allRelevantRabItems = $this->getRelevantRabItems($report->activities, $package->id);

            // Langkah 2: Tempelkan progres HANYA pada item yang ada di koleksi ini
            $this->attachProgressToItems($allRelevantRabItems, $report->activities, $package->id, $selectedDate);
            
            // Langkah 3: Bangun pohon dan kalkulasi bobot
            $activityTree = $this->buildTree($allRelevantRabItems);
        }
        
        $viewData = [
            'package' => $package,
            'report' => $report,
            'selectedDate' => $selectedDate->toDateString(),
            'activityTree' => $activityTree,
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('daily_reports.partials._summary-content', $viewData)->render(),
                'date_header' => \Carbon\Carbon::parse($selectedDate)->isoFormat('dddd, D MMMM YYYY'),
            ]);
        }

        return view('daily_reports.index', $viewData);
    }
    
    /**
     * GANTI: Logika getRelevantRabItems() disempurnakan.
     * Menggunakan pendekatan yang lebih pasti untuk mendapatkan semua item dan induknya.
     */
    private function getRelevantRabItems($activities, $packageId)
    {
        $reportedItemIds = $activities->pluck('rab_item_id')->filter()->unique();
        if ($reportedItemIds->isEmpty()) {
            return collect();
        }

        // Cari tahu apa saja item level 1 (induk utama) dari pekerjaan yang dilaporkan
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

        // Ambil semua item di dalam package yang merupakan bagian dari cabang-cabang induk utama tersebut
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

    /**
     * GANTI: Logika attachProgressToItems()
     * Memastikan semua properti progres diinisialisasi dengan benar.
     */
    private function attachProgressToItems($rabItems, $activities, $packageId, $selectedDate)
    {
        foreach ($rabItems as $item) {
            $activity = $activities->firstWhere('rab_item_id', $item->id);
            
            // Inisialisasi semua nilai progres menjadi 0
            $item->is_reported_activity = false;
            $item->progress_volume_period = 0;
            $item->previous_progress_volume = 0;
            $item->progress_weight_period = 0;
            $item->previous_progress_weight = 0;

            // Jika item ini dilaporkan, baru isi data progresnya
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
     * GANTI: Logika buildTree()
     * Ini adalah perubahan KUNCI. Fungsi ini sekarang mengakumulasi bobot
     * 'Lalu' dan 'Periode Ini' secara terpisah.
     */
    private function buildTree($items, $parentId = null)
    {
        $branch = collect();
        $childrenOfParent = $items->where('parent_id', $parentId)->sortBy('id');

        foreach ($childrenOfParent as $item) {
            $children = $this->buildTree($items, $item->id);
            
            // Setel anak-anak ke item
            $item->children = $children;
            
            // Kalkulasi Bobot Kontrak untuk sub-item (jika belum ada)
            if (is_null($item->volume)) {
                $item->weighting = $children->sum('weighting');
            }

            // Akumulasi Bobot Progres dari anak-anaknya
            $item->previous_progress_weight += $children->sum('previous_progress_weight');
            $item->progress_weight_period += $children->sum('progress_weight_period');

            $branch->push($item);
        }

        // HANYA tampilkan cabang yang punya progres atau bobot kontrak
        return $branch->filter(function ($item) {
            return ($item->previous_progress_weight + $item->progress_weight_period) > 0 || $item->weighting > 0;
        });
    }

    public function create(Request $request, Package $package)
    {
        $targetDate = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();

        $report = $package->dailyReports()
                               ->firstOrCreate(
                                   [
                                       'report_date' => $targetDate,
                                       'package_id' => $package->id
                                   ],
                                   [
                                       'user_id' => Auth::id()
                                   ]
                               );
        
        return redirect()->route('daily_reports.edit', ['package' => $package->id, 'daily_report' => $report->id]);
    }

    public function edit(Package $package, DailyReport $daily_report)
    {
        if ($daily_report->personnel->isEmpty() && $daily_report->wasRecentlyCreated) {
            $lastReportWithPersonnel = $package->dailyReports()
                ->where('id', '!=', $daily_report->id)
                ->whereHas('personnel')
                ->latest('report_date')
                ->first();

            if ($lastReportWithPersonnel) {
                foreach ($lastReportWithPersonnel->personnel as $personnel) {
                    $daily_report->personnel()->create([
                        'role' => $personnel->role,
                        'company_type' => $personnel->company_type,
                        'count' => $personnel->count,
                    ]);
                }
            }
        }

        $daily_report->load(['weather', 'personnel', 'activities.rabItem', 'activities.user']);

        return view('daily_reports.edit', [
            'package' => $package,
            'report' => $daily_report,
        ]);
    }
 
    public function storeWeather(Request $request, DailyReport $daily_report)
    {
        $validated = $request->validate([
            'time' => 'required',
            'condition' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $weather_log = $daily_report->weather()->create($validated);
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data cuaca berhasil ditambahkan.',
                'html' => view('daily_reports.partials.weather-row', ['weather_log' => $weather_log])->render(),
            ]);
        }
        return back()->with('success', 'Data cuaca berhasil ditambahkan.');
    }

    public function destroyWeather(DailyReportWeather $weather_log)
    {
        $weather_log->delete();
        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Data cuaca berhasil dihapus.']);
        }
        return back()->with('success', 'Data cuaca berhasil dihapus.');
    }

    public function storePersonnel(Request $request, DailyReport $daily_report)
    {
        $validated = $request->validate([
            'role' => 'required|string|max:255',
            'company_type' => 'required|string|max:255',
            'count' => 'required|integer|min:0',
        ]);
        $personnel = $daily_report->personnel()->updateOrCreate(
            ['role' => $validated['role'], 'company_type' => $validated['company_type']],
            ['count' => $validated['count']]
        );
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data personil berhasil diperbarui.',
                'personnel' => $personnel,
                'html' => view('daily_reports.partials.personnel-row', ['personnel' => $personnel])->render(),
            ]);
        }
        return back()->with('success', 'Data personil berhasil diperbarui.');
    }

    public function destroyPersonnel(DailyReportPersonnel $personnel_log)
    {
        $personnel_log->delete();
        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Data personil berhasil dihapus.']);
        }
        return back()->with('success', 'Data personil berhasil dihapus.');
    }
}