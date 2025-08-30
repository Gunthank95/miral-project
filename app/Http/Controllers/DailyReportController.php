<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\DailyReport;
use App\Models\DailyLog;
use App\Models\DailyReportWeather;
use App\Models\DailyReportPersonnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\ReportBuilderService;

class DailyReportController extends Controller
{
    protected $reportBuilder;

    public function __construct(ReportBuilderService $reportBuilder)
    {
        $this->reportBuilder = $reportBuilder;
    }

    public function index(Request $request, Package $package)
	{
		$dateInput = $request->input('date');
		$selectedDate = $dateInput ? new \Carbon\Carbon($dateInput) : \Carbon\Carbon::today();
		$filter = $request->input('filter', 'this_period');

		$report = $package->dailyReports()
			->whereDate('report_date', $selectedDate->format('Y-m-d'))
			->with([
				'weather', 
				'personnel',
				'activities.materials.material',
				'activities.equipment'
			])
			->first();

		// Inisialisasi variabel
		$activityTree = collect();
		$totalProgress = 0;
		$allMaterials = collect();
		$allEquipment = collect();

		if ($report) {
			// Panggil service dan ambil hasilnya
			$reportData = $this->reportBuilder->generateDailyReport($report, $package->id, $filter);
			
			// Ekstrak data dari hasil
			$activityTree = $reportData['tree'];
			$totalProgress = $reportData['totalProgress'];

			// Mengumpulkan material dari aktivitas
			$allMaterials = $report->activities->flatMap(function ($activity) {
				return $activity->materials;
			})->groupBy('material.name')->map(function ($items) {
				return [
					'quantity' => $items->sum('quantity'),
					'unit' => $items->first()->material->unit ?? ''
				];
			});

			// Mengumpulkan peralatan dari aktivitas
			$allEquipment = $report->activities->flatMap(function ($activity) {
				return $activity->equipment;
			})->groupBy('name')->map(function ($items) {
				return $items->sum('quantity');
			});
		}
		
		return view('daily_reports.index', [
			'package' => $package,
			'selectedDate' => $selectedDate,
			'report' => $report,
			'activityTree' => $activityTree,
			'allMaterials' => $allMaterials,
			'allEquipment' => $allEquipment,
			'totalProgress' => $totalProgress, // Kirim total progres yang sudah benar
		]);
	}

    public function create(Request $request, Package $package)
    {
        $targetDate = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();

        $report = $package->dailyReports()->firstOrCreate(
            ['report_date' => $targetDate, 'package_id' => $package->id],
            ['user_id' => Auth::id()]
        );
        
        return redirect()->route('daily_reports.edit', ['package' => $package->id, 'daily_report' => $report->id]);
    }

    // PERBAIKAN PADA FUNGSI EDIT
    public function edit(Package $package, DailyReport $daily_report)
    {
        // Pastikan laporan yang diakses adalah milik paket yang benar
        if ($daily_report->package_id !== $package->id) {
            abort(404);
        }

        // Otomatis salin data personil dari laporan terakhir jika ini laporan baru
        if ($daily_report->personnel->isEmpty() && $daily_report->wasRecentlyCreated) {
            $lastReport = $package->dailyReports()
                ->where('id', '!=', $daily_report->id)
                ->whereHas('personnel')
                ->latest('report_date')
                ->first();

            if ($lastReport) {
                foreach ($lastReport->personnel as $personnel) {
                    $daily_report->personnel()->create($personnel->only(['role', 'company_type', 'count']));
                }
            }
        }

        $daily_report->load(['weather', 'personnel', 'activities.rabItem', 'activities.user']);

        return view('daily_reports.edit', [
            'package' => $package,
            'daily_report' => $daily_report,
        ]);
    }

    // ... sisa fungsi lainnya (storeWeather, destroyWeather, dst.) tetap sama ...
    public function update(Request $request, DailyReport $daily_report)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $daily_report->update($validated);

        return back()->with('success', 'Catatan berhasil diperbarui.');
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