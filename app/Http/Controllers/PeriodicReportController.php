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
		$validated = $request->validated();

		// 1. Tentukan tanggal
		$startDateString = $validated['start_date'] ?? now()->startOfMonth()->toDateString();
		$endDateString = $validated['end_date'] ?? now()->endOfMonth()->toDateString();
		$filter = $validated['filter'] ?? 'all';

		// 2. Konversi tanggal menjadi objek Carbon untuk Service
		$startDate = \Carbon\Carbon::parse($startDateString);
		$endDate = \Carbon\Carbon::parse($endDateString);

		// 3. Panggil service untuk mendapatkan data laporan
		$reportData = $this->reportBuilder->generatePeriodicReport($package, $startDate, $endDate, $filter);

		// 4. PERBAIKAN UTAMA: Bangun array untuk view secara manual dan aman
		$viewData = [
			'package' => $package,
			'startDate' => $startDateString,
			'endDate' => $endDateString,
			'filter' => $filter,
			// Ambil setiap item dari hasil service, berikan nilai default (koleksi kosong) jika tidak ada
			'rabTree' => $reportData->get('rabTree', collect()),
			'allPersonnel' => $reportData->get('allPersonnel', collect()),
			'allMaterials' => $reportData->get('allMaterials', collect()),
			'allEquipment' => $reportData->get('allEquipment', collect()),
			'allWeather' => $reportData->get('allWeather', collect()),
		];

		// 5. Kirim data ke view yang sesuai
		if ($request->has('print')) {
			return view('periodic_reports.print', $viewData);
		}

		return view('periodic_reports.index', $viewData);
	}
}