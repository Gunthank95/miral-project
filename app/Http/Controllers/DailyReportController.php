<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\DailyReport;
use App\Models\DailyLog;
use App\Models\DailyReportWeather;
use App\Models\DailyReportPersonnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- PERBAIKAN UTAMA DI SINI
use Carbon\Carbon;

class DailyReportController extends Controller
{
    /**
     * Menampilkan ringkasan laporan harian untuk tanggal yang dipilih.
     */
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

        if ($report) {
            foreach ($report->activities as $activity) {
                $previousVolume = DailyLog::where('package_id', $package->id)
                                          ->where('rab_item_id', $activity->rab_item_id)
                                          ->whereDate('log_date', '<', $selectedDate)
                                          ->sum('progress_volume');
                $activity->previous_progress_volume = $previousVolume;
            }
        }
        
        $viewData = [
            'package' => $package,
            'report' => $report,
            'selectedDate' => $selectedDate->toDateString(),
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
     * Membuat draft laporan baru untuk tanggal yang dipilih atau redirect ke yang sudah ada.
     */
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

    /**
     * Menampilkan halaman pembuatan/edit laporan harian.
     */
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

    //(Fungsi-fungsi store dan destroy untuk Weather dan Personnel) 
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