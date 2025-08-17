<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\DailyLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PeriodicReportController extends Controller
{
    public function index(Request $request, Package $package)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $reports = collect(); // Default koleksi kosong
        $groupedActivities = collect();

        if ($startDate && $endDate) {
            // Ambil semua laporan dalam rentang tanggal
            $reports = $package->dailyReports()
                                ->whereBetween('report_date', [$startDate, $endDate])
                                ->with('personnel', 'weather', 'activities')
                                ->get();

            // Ambil semua aktivitas dalam rentang tanggal yang dipilih
            $activitiesThisPeriod = DailyLog::where('package_id', $package->id)
                                    ->whereBetween('log_date', [$startDate, $endDate])
                                    ->with('rabItem')
                                    ->get();

            $groupedActivities = $activitiesThisPeriod->groupBy('rab_item_id');

            foreach ($groupedActivities as $rab_item_id => $activities) {
                $previousVolume = DailyLog::where('package_id', $package->id)
                                      ->where('rab_item_id', $rab_item_id)
                                      ->whereDate('log_date', '<', $startDate)
                                      ->sum('progress_volume');

                $activities->previous_progress_volume = $previousVolume;
            }
        }

        return view('periodic_reports.index', [
            'package' => $package,
            'reports' => $reports, // Kirim semua data laporan
            'groupedActivities' => $groupedActivities,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
	
	/**
	 * Menyiapkan dan menampilkan halaman versi cetak.
	 */
	public function print(Request $request, Package $package)
	{
		// Logikanya sama persis dengan fungsi index()
		$startDate = $request->input('start_date');
		$endDate = $request->input('end_date');
		$groupedActivities = collect();

		if ($startDate && $endDate) {
			$activitiesThisPeriod = DailyLog::where('package_id', $package->id)
									->whereBetween('log_date', [$startDate, $endDate])
									->with('rabItem')
									->get();

			$groupedActivities = $activitiesThisPeriod->groupBy('rab_item_id');

			foreach ($groupedActivities as $rab_item_id => $activities) {
				$previousVolume = DailyLog::where('package_id', $package->id)
									  ->where('rab_item_id', $rab_item_id)
									  ->whereDate('log_date', '<', $startDate)
									  ->sum('progress_volume');

				$activities->previous_progress_volume = $previousVolume;
			}
		}

		// Perbedaannya hanya di sini: memanggil view 'print.blade.php'
		return view('periodic_reports.print', [
			'package' => $package,
			'groupedActivities' => $groupedActivities,
			'startDate' => $startDate,
			'endDate' => $endDate,
		]);
	}
}