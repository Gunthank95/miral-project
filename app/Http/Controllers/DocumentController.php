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
			->with(['rabItems', 'user', 'approvals.user']) // PERBAIKI: Ambil juga data 'approvals' dan 'user' yang me-review
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
	
	public function storeReview(Request $request, Document $document)
	{
		$request->validate([
			'status' => 'required|string',
			'notes' => 'nullable|string',
			'continue_to_owner' => 'required|boolean',
		]);

		// Cari pengajuan awal dari kontraktor
		$originalSubmission = $document->approvals()->where('status', 'submitted')->first();

		if ($originalSubmission) {
			// Buat catatan review baru sebagai "anak" dari pengajuan awal
			$document->approvals()->create([
				'parent_id' => $originalSubmission->id,
				'user_id' => \Auth::id(),
				'status' => $request->status,
				'notes' => $request->notes,
			]);
		}

		// Update status dokumen utama
		$document->update(['status' => $request->status]);

		// Logika untuk Owner akan kita tambahkan nanti

		return back()->with('success', 'Review berhasil disimpan.');
	}
	
	public function destroy(Package $package, Document $document)
    {
        // Hapus file dari penyimpanan
        \Illuminate\Support\Facades\Storage::disk('public')->delete($document->file_path);
        
        // Hapus catatan dari database
        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }
	
	/**
     * TAMBAHKAN: Fungsi untuk menampilkan halaman edit dokumen.
     */
    public function edit(Package $package, Document $document)
    {
        // Ambil data RAB (Sub Item Utama) untuk dropdown
        $mainRabItems = \App\Models\RabItem::where('package_id', $package->id)
            ->whereNull('parent_id')
            ->orderBy('item_number', 'asc')
            ->get();
        
        // Ambil ID dari item pekerjaan yang sudah terhubung dengan dokumen ini
        $selectedRabItems = $document->rabItems()->pluck('rab_items.id')->toArray();

        return view('documents.edit', compact('package', 'document', 'mainRabItems', 'selectedRabItems'));
    }

    /**
     * TAMBAHKAN: Fungsi untuk menyimpan perubahan pada dokumen.
     */
    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'document_number' => 'nullable|string|max:255',
            'drawing_numbers' => 'nullable|string',
            'rab_items' => 'nullable|array',
        ]);

        $document->update($validated);

        if ($request->has('rab_items')) {
            $document->rabItems()->sync($request->rab_items);
        } else {
            $document->rabItems()->detach();
        }

        return redirect()->route('documents.index', ['package' => $document->package_id])
                         ->with('success', 'Dokumen berhasil diperbarui.');
    }
}