<?php

namespace App\Http\Controllers;

use App\Models\DailyLog;
use App\Models\Package;
use App\Models\PlannedProgress; // Pastikan ini di-import
use App\Models\Schedule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SCurveController extends Controller
{
    /**
     * Menampilkan halaman Kurva S.
     */
    public function index(Package $package, Request $request)
    {
        // Panggil method private untuk mengambil dan memproses data
        $processedData = $this->getProcessedSCurveData($package, $request);

        // Ubah format 'chartData' menjadi JSON khusus untuk view ini
        $processedData['chartData'] = json_encode($processedData['chartData']);

        return view('s_curve.index', $processedData);
    }

    /**
     * Menyimpan bobot rencana mingguan yang diinput secara inline.
     */
    public function storePlan(Package $package, Request $request)
    {
        $validated = $request->validate([
            'week_start_date' => 'required|date_format:Y-m-d',
            'weight' => 'required|numeric|min:0|max:100',
        ]);

        PlannedProgress::updateOrCreate(
            [
                'package_id' => $package->id,
                'week_start_date' => $validated['week_start_date'],
            ],
            [
                'weight' => $validated['weight'],
            ]
        );

        // Setelah menyimpan, hitung ulang seluruh data dan kirim kembali sebagai JSON
        $processedData = $this->getProcessedSCurveData($package, $request);
        
        return response()->json($processedData);
    }

    /**
     * "Mesin" utama untuk memproses semua data Kurva S.
     * Dapat dipanggil oleh index() dan storePlan().
     */
    private function getProcessedSCurveData(Package $package, Request $request)
    {
        $defaultStartDate = $package->project->start_date ? Carbon::parse($package->project->start_date) : Carbon::parse(Schedule::where('package_id', $package->id)->min('start_date'));
        $defaultEndDate = $package->project->end_date ? Carbon::parse($package->project->end_date) : Carbon::parse(Schedule::where('package_id', $package->id)->max('end_date'));
        
        $filterStartDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : $defaultStartDate;
        $filterEndDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : $defaultEndDate;
        $reportingDay = (int) $request->input('reporting_day', Carbon::SATURDAY);

        // Jika tanggal tidak valid, kembalikan data kosong
        if (!$filterStartDate || !$filterEndDate) {
            return [
                'package' => $package, 'sCurveData' => [], 
                'chartData' => ['labels' => [], 'planned' => [], 'actual' => []],
                'error' => 'Tanggal mulai atau selesai proyek belum diatur.',
                'filterStartDate' => '', 'filterEndDate' => '', 
                'selectedReportingDay' => $reportingDay
            ];
        }
        
        // Kalkulasi Realisasi
        $totalProjectValue = $package->rabItems()->sum(DB::raw('volume * unit_price'));
        $dailyActualWeights = [];
        if ($totalProjectValue > 0) {
            $allDailyLogs = DailyLog::with('rabItem')->where('package_id', $package->id)->whereBetween('log_date', [$filterStartDate, $filterEndDate])->get();
            foreach ($allDailyLogs as $log) {
                if (!$log->rabItem || is_null($log->rabItem->volume) || is_null($log->rabItem->unit_price) || $log->rabItem->volume == 0) continue;
                $itemValue = $log->rabItem->volume * $log->rabItem->unit_price;
                $itemWeight = ($itemValue / $totalProjectValue) * 100;
                $progressWeight = ($log->progress_volume / $log->rabItem->volume) * $itemWeight;
                $logDateStr = Carbon::parse($log->log_date)->toDateString();
                if (!isset($dailyActualWeights[$logDateStr])) $dailyActualWeights[$logDateStr] = 0;
                $dailyActualWeights[$logDateStr] += $progressWeight;
            }
        }
        
        // Ambil data Rencana dari database
        $manualPlans = PlannedProgress::where('package_id', $package->id)
            ->whereBetween('week_start_date', [$filterStartDate, $filterEndDate])
            ->pluck('weight', 'week_start_date');

        $sCurveData = [];
        $cumulativePlanned = 0;
        $cumulativeActual = 0;
        $weekNumber = 1;
        
        $startOfWeekDay = ($reportingDay + 1) % 7;
        $weeklyPeriods = CarbonPeriod::create($filterStartDate->copy()->startOfWeek($startOfWeekDay), '1 week', $filterEndDate->copy()->endOfWeek($reportingDay));
        
        foreach ($weeklyPeriods as $weekStartDate) {
            $weekEndDate = $weekStartDate->copy()->endOfWeek($reportingDay);
            
            $weekStartDateString = $weekStartDate->toDateString();
            $weeklyPlannedWeight = (float) ($manualPlans[$weekStartDateString] ?? 0);
            
            $weeklyActualWeight = 0;
            $weekPeriod = CarbonPeriod::create($weekStartDate, $weekEndDate);
            foreach ($weekPeriod as $day) {
                $weeklyActualWeight += $dailyActualWeights[$day->toDateString()] ?? 0;
            }

            $cumulativePlanned += $weeklyPlannedWeight;
            $cumulativeActual += $weeklyActualWeight;
            
            $sCurveData[] = [
                'week_label' => 'Minggu ke-' . $weekNumber++,
                'start_date' => $weekStartDate->format('d-m-Y'),
                'start_date_raw' => $weekStartDateString,
                'end_date' => $weekEndDate->format('d-m-Y'),
                'planned_weekly' => $weeklyPlannedWeight,
                'planned_cumulative' => $cumulativePlanned,
                'actual_cumulative' => min($cumulativeActual, 100),
                'deviation' => min($cumulativeActual, 100) - $cumulativePlanned,
            ];
        }

        $chartData = [
            'labels' => collect($sCurveData)->pluck('week_label'),
            'planned' => collect($sCurveData)->pluck('planned_cumulative'),
            'actual' => collect($sCurveData)->pluck('actual_cumulative'),
        ];
        
        return [
            'package' => $package,
            'sCurveData' => $sCurveData,
            'chartData' => $chartData,
            'filterStartDate' => $filterStartDate->toDateString(),
            'filterEndDate' => $filterEndDate->toDateString(),
            'selectedReportingDay' => $reportingDay,
        ];
    }
}