<?php

namespace App\Http\Controllers;

// ... (Use statements tetap sama) ...
use App\Models\Package;
use App\Models\DailyReport;
use App\Models\DailyLog;
use App\Models\RabItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DailyReportController extends Controller
{
    // ... (Fungsi index() tetap sama) ...
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

        if ($report) {
            $relevantRabItems = $this->getRelevantRabItems($report->activities); // Fungsi ini yang kita perbaiki
            $this->attachProgressToItems($relevantRabItems, $report->activities, $package->id, $selectedDate);
            $activityTree = $this->buildTree($relevantRabItems);
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
     * GANTI: Logika fungsi getRelevantRabItems()
     * Fungsi ini diubah menjadi lebih efisien dan andal dalam mengumpulkan semua item
     * yang relevan (item yang dilaporkan beserta semua induknya).
     */
    private function getRelevantRabItems($activities)
    {
        $reportedItemIds = $activities->pluck('rab_item_id')->filter()->unique();

        if ($reportedItemIds->isEmpty()) {
            return collect();
        }

        $allItemIds = $reportedItemIds->toArray();
        $parentIdsToFetch = $reportedItemIds;

        // Loop untuk mencari semua parent ID sampai ke akar
        while ($parentIdsToFetch->isNotEmpty()) {
            // Ambil parent_id dari item-item saat ini
            $items = RabItem::whereIn('id', $parentIdsToFetch->toArray())->get(['id', 'parent_id']);
            $parentIds = $items->pluck('parent_id')->filter()->unique();

            // Cek apakah ada parent baru yang belum ada di daftar
            $newParentIds = array_diff($parentIds->toArray(), $allItemIds);

            if (empty($newParentIds)) {
                break; // Berhenti jika tidak ada parent baru
            }
            
            // Tambahkan parent baru ke daftar dan siapkan untuk iterasi berikutnya
            $allItemIds = array_merge($allItemIds, $newParentIds);
            $parentIdsToFetch = collect($newParentIds);
        }

        // Setelah semua ID terkumpul, ambil data lengkapnya dari database
        return RabItem::whereIn('id', $allItemIds)->get()->keyBy('id');
    }


    // ... (Sisa fungsi lainnya: attachProgressToItems(), buildTree(), create(), edit(), dll. tetap sama) ...
    private function attachProgressToItems($rabItems, $activities, $packageId, $selectedDate)
    {
        foreach ($rabItems as $item) {
            $activity = $activities->firstWhere('rab_item_id', $item->id);
            
            $previousVolume = DailyLog::where('package_id', $packageId)
                                      ->where('rab_item_id', $item->id)
                                      ->whereDate('log_date', '<', $selectedDate)
                                      ->sum('progress_volume');

            $item->progress_volume = $activity->progress_volume ?? 0;
            $item->previous_progress_volume = $previousVolume;
            $item->is_reported_activity = (bool)$activity;

            $item->progress_weight_period = 0;
            $item->previous_progress_weight = 0;

            if ($item->volume > 0) {
                $item->progress_weight_period = ($item->progress_volume / $item->volume) * $item->weighting;
                $item->previous_progress_weight = ($item->previous_progress_volume / $item->volume) * $item->weighting;
            }
        }
    }

    private function buildTree($items, $parentId = null)
    {
        $branch = collect();

        foreach ($items->where('parent_id', $parentId)->sortBy('id') as $item) {
            $children = $this->buildTree($items, $item->id);
            
            if ($children->isNotEmpty() || $item->is_reported_activity) {
                $item->children = $children;

                $item->progress_weight_period += $children->sum('progress_weight_period');
                $item->previous_progress_weight += $children->sum('previous_progress_weight');
                
                $item->progress_volume += $children->sum('progress_volume');
                $item->previous_progress_volume += $children->sum('previous_progress_volume');

                $branch->push($item);
            }
        }

        return $branch;
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