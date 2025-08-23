<?php

namespace App\Http\Controllers;

use App\Models\Package;
//use App\Models\RabItem;
//use App\Models\DailyLog;
//use App\Models\DailyReport; // TAMBAHKAN: Panggil model DailyReport
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\ReportBuilderService; // TAMBAHKAN

class PeriodicReportController extends Controller
{
    // TAMBAHKAN: Properti dan constructor untuk inject service
    protected $reportBuilder;

    public function __construct(ReportBuilderService $reportBuilder)
    {
        $this->reportBuilder = $reportBuilder;
    }

    /**
     * GANTI: Logika index() menjadi lebih ramping
     */
    public function index(Request $request, Package $package)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'filter' => 'nullable|string|in:all,this_period,until_now',
        ]);

        $startDate = $validated['start_date'] ?? Carbon::now()->startOfMonth()->toDateString();
        $endDate = $validated['end_date'] ?? Carbon::now()->endOfMonth()->toDateString();
        $filter = $validated['filter'] ?? 'all';

        // GANTI: Panggil service untuk mendapatkan semua data laporan periodik
        $reportData = $this->reportBuilder->generatePeriodicReport($package, $startDate, $endDate, $filter);

        $viewData = array_merge([
            'package' => $package,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filter' => $filter,
        ], $reportData);

        if ($request->has('print')) {
            return view('periodic_reports.print', $viewData);
        }

        return view('periodic_reports.index', $viewData);
    }
}