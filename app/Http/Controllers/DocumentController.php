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
        // 1. Siapkan daftar kategori.
        $categories = [
            'shop_drawing' => 'Shop Drawing',
        ];

        // 2. Tentukan tab mana yang sedang aktif.
        $activeCategory = $request->input('category', 'shop_drawing');

        // 3. Ambil SEMUA dokumen dari paket ini sekaligus.
        $allDocuments = \App\Models\Document::where('package_id', $package->id)
            ->with(['rabItems', 'user', 'approvals.user'])
            ->latest()
            ->get();

        // 4. Kelompokkan semua dokumen tersebut berdasarkan kategori.
        $documentsByCategory = $allDocuments->groupBy(function ($item, $key) {
			// Membuat semua nama kategori menjadi standar: huruf kecil dan pakai underscore
			return str_replace(' ', '_', strtolower($item->category));
		});
        
        // 5. Kirim semua data yang sudah siap ke halaman tampilan.
        return view('documents.index', compact('package', 'categories', 'activeCategory', 'documentsByCategory'));
    }
	
    /**
     * Menampilkan form untuk mengunggah dokumen baru.
     */
    public function create(Request $request, Package $package)
    {
        $this->authorize('create', Document::class);
		$categoryKey = $request->input('category');
        $categories = [
            'for_con' => 'For-Con Drawing',
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
	
	public function createSubmission(Package $package)
	{
		$this->authorize('create', Document::class);

		// Ambil data RAB (hanya sub item utama) untuk dropdown
		$mainRabItems = \App\Models\RabItem::where('package_id', $package->id)
			->whereNull('parent_id')
			->orderBy('item_number', 'asc')
			->get();

		// Kirim data yang dibutuhkan ke view
		return view('documents.create_submission', [
			'package' => $package,
			'mainRabItems' => $mainRabItems,
		]);
	}
	
	/**
     * Menampilkan form untuk mengunggah revisi sebuah dokumen.
     */
    public function createRevision(Package $package, Document $document)
    {
        // Periksa apakah pengguna berhak membuat dokumen baru (aturan sama dengan membuat dari awal)
        $this->authorize('create', Document::class);

        // Ambil data RAB (Sub Item Utama) untuk dropdown
        $mainRabItems = \App\Models\RabItem::where('package_id', $package->id)
            ->whereNull('parent_id')
            ->orderBy('item_number', 'asc')
            ->get();
        
        // Ambil ID dari item pekerjaan yang sudah terhubung dengan DOKUMEN ASLI
        $selectedRabItems = $document->rabItems()->pluck('rab_items.id')->toArray();

        // Kirim data ke view yang sama dengan form pengajuan, 
        // tapi sekarang kita juga mengirim data '$document' sebagai '$originalDocument'
        return view('documents.create_submission', [
            'package' => $package,
            'mainRabItems' => $mainRabItems,
            'originalDocument' => $document, // Data dokumen lama untuk mengisi form
            'selectedRabItems' => $selectedRabItems,
        ]);
    }

    /**
     * Menyimpan dokumen baru.
     */
    public function store(Request $request)
	{
		// (VALIDASI BIARKAN SAMA SEPERTI KODE LAMA ANDA)
		$this->authorize('create', Document::class);
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
	
	/**
     * Menyimpan dokumen baru yang diajukan melalui form detail (submission form).
     * Fungsi ini bisa menangani dokumen baru maupun dokumen revisi.
     */
    public function storeSubmission(Request $request, Package $package)
    {
        $this->authorize('create', Document::class);

        // 1. Validasi semua input dari form
        $validated = $request->validate([
            'document_number' => 'required|string|max:255',
            'drawing_title' => 'required|string|max:255',
            'drawing_number' => 'required|string|max:255',
            'files' => 'required|array',
            'files.*' => 'file|mimes:pdf,dwg,zip,rar|max:20480', // Setiap file max 20MB
            'rab_items' => 'nullable|array',
        ]);

        // 2. Buat "Surat Pengantar" (entri di tabel documents)
        $document = Document::create([
            'package_id' => $package->id,
            'user_id' => Auth::id(),
            'document_number' => $validated['document_number'],
            'status' => 'pending', // Status awal pengajuan
            'category' => 'shop_drawing', // Dibuat statis karena halaman ini khusus shop drawing
             // Kita isi title & name dari nomor dokumen agar tidak kosong
            'title' => 'Shop Drawing Submission: ' . $validated['document_number'],
            'name' => 'Shop Drawing Submission: ' . $validated['document_number'],
        ]);

        // 3. Simpan setiap file yang diunggah
        foreach ($request->file('files') as $file) {
            $path = $file->store('documents', 'public');
            $document->files()->create([
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
            ]);
        }

        // 4. Simpan detail gambar (saat ini baru satu)
        $drawingDetail = $document->drawingDetails()->create([
            'drawing_number' => $validated['drawing_number'],
            'drawing_title' => $validated['drawing_title'],
            'revision' => 0,
            'status' => 'pending',
        ]);

        // 5. Hubungkan item pekerjaan ke detail gambar
        if (!empty($validated['rab_items'])) {
            // Kita siapkan data untuk tabel pivot
            $rabData = [];
            foreach ($validated['rab_items'] as $rabId) {
                $rabData[$rabId] = ['completion_status' => 'belum'];
            }
            $drawingDetail->rabItems()->sync($rabData);
        }

        // 6. Arahkan kembali ke halaman utama dengan pesan sukses
        return redirect()->route('documents.index', ['package' => $package->id])
                         ->with('success', 'Shop Drawing berhasil diajukan dan sedang menunggu review.');
    }
	
	public function storeReview(Request $request, Package $package, Document $document)
    {
        // 1. Validasi input: Memastikan status yang dipilih adalah salah satu dari tiga ini.
        $this->authorize('review', $document);
		$validated = $request->validate([
            'status' => 'required|in:approved,revision,rejected', // Ditambahkan status 'revision'
            'notes' => 'nullable|string',
        ]);

        // 2. Memperbarui status utama pada dokumen
        $document->status = $validated['status'];
        $document->save();

        // 3. Membuat catatan riwayat persetujuan baru
        DocumentApproval::create([
            'document_id' => $document->id,
            'user_id' => Auth::id(),
            'status' => $validated['status'],
            'notes' => $validated['notes'],
        ]);

        // 4. Kembali ke halaman sebelumnya dengan pesan sukses
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
    public function edit(Package $package, Document $shop_drawing)
    {
        // Ambil data RAB (Sub Item Utama) untuk dropdown
        $mainRabItems = \App\Models\RabItem::where('package_id', $package->id)
            ->whereNull('parent_id')
            ->orderBy('item_number', 'asc')
            ->get();
        
        // Ambil ID dari item pekerjaan yang sudah terhubung dengan dokumen ini
        $selectedRabItems = $shop_drawing->rabItems()->pluck('rab_items.id')->toArray();

		return view('documents.edit', compact('package', 'shop_drawing', 'mainRabItems', 'selectedRabItems'));
    }

    /**
     * TAMBAHKAN: Fungsi untuk menyimpan perubahan pada dokumen.
     */
    public function update(Request $request, Document $shop_drawing)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'document_number' => 'nullable|string|max:255',
            'drawing_numbers' => 'nullable|string',
            'rab_items' => 'nullable|array',
        ]);

        $shop_drawing->update($validated);

		if ($request->has('rab_items')) {
			$shop_drawing->rabItems()->sync($request->rab_items);
		} else {
			$shop_drawing->rabItems()->detach();
		}

		return redirect()->route('documents.index', ['package' => $shop_drawing->package_id])
						 ->with('success', 'Dokumen berhasil diperbarui.');
	}
}