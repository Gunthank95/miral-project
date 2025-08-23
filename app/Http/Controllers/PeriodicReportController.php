<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Carbon\Carbon;
use App\Services\ReportBuilderService;
// TAMBAHKAN: Panggil Form Request yang baru
use App\Http\Requests\PeriodicReportFilterRequest; 
// HAPUS: use Illuminate\Http\Request; sudah tidak kita gunakan di sini

class PeriodicReportController extends Controller
{
    protected $reportBuilder;

    public function __construct(ReportBuilderService $reportBuilder)
    {
        $this->reportBuilder = $reportBuilder;
    }

    /**
     * GANTI: Ubah tipe parameter dari Request menjadi PeriodicReportFilterRequest
     */
    public function index(PeriodicReportFilterRequest $request, Package $package)
    {
        // HAPUS: Baris $request->validate(...) tidak diperlukan lagi.
        $validated = $request->validated(); // Ambil data yang sudah lolos validasi

        $startDate = $validated['start_date'] ?? Carbon::now()->startOfMonth()->toDateString();
        $endDate = $validated['end_date'] ?? Carbon::now()->endOfMonth()->toDateString();
        $filter = $validated['filter'] ?? 'all';

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