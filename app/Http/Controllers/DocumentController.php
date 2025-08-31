<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Document; // Pastikan ini ditambahkan
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    public function index(Request $request, Package $package)
	{
		// 1. Siapkan daftar kategori. Kunci (key) sekarang menggunakan garis bawah
		// agar cocok dengan URL dan tab di halaman tampilan.
		$categories = [
			'shop_drawing' => 'Shop Drawing',
			'as_built_drawing' => 'As-Built Drawing',
			'for_con_drawing' => 'For-Con Drawing',
			'metode_kerja' => 'Metode Kerja',
			'lainnya' => 'Lainnya',
		];

		// 2. Tentukan tab mana yang sedang aktif, defaultnya adalah 'shop_drawing'
		$activeCategory = $request->input('category', 'shop_drawing');

		// 3. Ambil SEMUA dokumen dari paket ini sekaligus
		$allDocuments = \App\Models\Document::where('package_id', $package->id)
			->with('rabItems', 'user') // Ambil juga relasi yang dibutuhkan
			->latest()
			->get();

		// 4. Buat variabel $documentsByCategory dengan mengelompokkan dokumen-dokumen tersebut
		$documentsByCategory = $allDocuments->groupBy('category');
		
		// 5. Kirim semua data yang sudah siap ke halaman tampilan
		return view('documents.index', compact('package', 'documentsByCategory', 'categories', 'activeCategory'));
	}
    /**
     * Menampilkan form untuk mengunggah dokumen baru.
     */
    public function create(Request $request, Package $package)
    {
        $categoryKey = $request->input('category');
        $categories = [
            'for_con' => 'For-Con Drawing',
            'metode_kerja' => 'Metode Kerja',
            'shop_drawing' => 'Shop Drawing',
            'as_built' => 'As-Built Drawing',
        ];

        // Pastikan kategori yang dikirim valid
        if (!array_key_exists($categoryKey, $categories)) {
            abort(404);
        }

        return view('documents.create', [
            'package' => $package,
            'categoryKey' => $categoryKey,
            'categoryName' => $categories[$categoryKey],
        ]);
    }
	
	public function createSubmission(Request $request, \App\Models\Package $package)
	{
		// PERBAIKI: Mengambil data RAB (Sub Item Utama) untuk dropdown pertama
		$mainRabItems = \App\Models\RabItem::where('package_id', $package->id)
			->whereNull('parent_id')
			->orderBy('item_number', 'asc') // Mengurutkan berdasarkan nomor item
			->get();

		return view('documents.create_submission', [
			'package' => $package,
			'mainRabItems' => $mainRabItems,
		]);
	}

    /**
     * Menyimpan dokumen baru.
     */
    public function store(Request $request)
	{
		// (VALIDASI BIARKAN SAMA SEPERTI KODE LAMA ANDA)
		$request->validate([
			'package_id' => 'required|exists:packages,id',
			'category' => 'required|string',
			'title' => 'required|string|max:255',
			'description' => 'nullable|string',
			'file' => 'required|file|mimes:pdf,jpg,jpeg,png,dwg|max:20480', // Max 20MB
		]);

		$package = \App\Models\Package::find($request->package_id);
		$project = $package->project;

		// SIMPAN FILE
		$filePath = $request->file('file')->store('documents', 'public');

		// TAMBAHKAN: Logika untuk menentukan apakah persetujuan diperlukan
		// =================================================================
		$requiresApproval = false;
		// Jika kategori yang diunggah adalah Shop Drawing atau Metode Kerja
		if (in_array($request->category, ['Shop Drawing', 'Metode Kerja'])) {
			$requiresApproval = true;
		}
		// =================================================================

		// BUAT DOKUMEN BARU
		$document = Document::create([
			'project_id' => $project->id,
			'package_id' => $package->id,
			'user_id' => \Auth::id(),
			'category' => $request->category,
			'title' => $request->title,
			'name' => $request->title,
			'description' => $request->description,
			'file_path' => $filePath,
			// Status awal sekarang tergantung pada kebutuhan persetujuan
			'status' => $requiresApproval ? 'pending' : 'approved', 
			// Simpan penanda ini ke database
			'requires_approval' => $requiresApproval, 
		]);
		
		// TAMBAHKAN: Jika butuh persetujuan, buat catatan awal di tabel approval
		// =================================================================
		if ($requiresApproval) {
			$document->approvals()->create([
				'user_id' => \Auth::id(),
				'status' => 'submitted', // Status awal di riwayat
				'notes' => 'Pengajuan awal dari Kontraktor.'
			]);
		}
		
		// TAMBAHKAN BLOK INI UNTUK MENGHUBUNGKAN DOKUMEN DENGAN RAB
		// =================================================================
		if ($request->has('rab_items')) {
			$document->rabItems()->sync($request->rab_items);
		}

		return redirect()->route('documents.index', ['package' => $package->id])
						 ->with('success', 'Dokumen berhasil diunggah.');
	}
}