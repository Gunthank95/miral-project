<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Document; // Pastikan ini ditambahkan
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    // GANTI method index() di dalam DocumentController

	public function index(Request $request, Package $package)
	{
		$categories = ['shop_drawing' => 'Shop Drawing'];
		$activeCategory = $request->input('category', 'shop_drawing');
		$user = Auth::user();

		// Ambil query dasar untuk dokumen
		$query = \App\Models\Document::where('package_id', $package->id)
			->with(['rabItems', 'user', 'approvals.user', 'files', 'drawingDetails']);

		// --- LOGIKA FILTER BARU UNTUK OWNER ---
		// Cek apakah user adalah Owner dan BUKAN MK di proyek ini
		$isPureOwner = $user->isOwnerInProject($package->project_id) && !$user->isMKInProject($package->project_id);

		if ($isPureOwner) {
			// Jika dia "hanya" Owner, tampilkan dokumen yang menunggunya saja
			$query->where('status', 'menunggu_persetujuan_owner');
		}
		// --- AKHIR LOGIKA FILTER ---

		// Lanjutkan query seperti biasa
		$allDocuments = $query->latest()->get();

		$documentsByCategory = $allDocuments->groupBy(function ($item, $key) {
			return str_replace(' ', '_', strtolower($item->category));
		});
		
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

		// 1. Validasi semua input dari form, termasuk array yang baru
		$validated = $request->validate([
			'document_number' => 'required|string|max:255',
			'drawings' => 'required|array|min:1',
			'drawings.*.title' => 'required|string|max:255',
			'drawings.*.number' => 'required|string|max:255',
			'files' => 'required|array',
			'files.*' => 'file|mimes:pdf,dwg,zip,rar|max:20480',
			'rab_items' => 'nullable|array',
			'rab_items.*.id' => 'required|exists:rab_items,id',
			'rab_items.*.completion_status' => 'required|in:lengkap,belum_lengkap',
		]);

		// Menggunakan DB Transaction agar semua proses berhasil atau gagal bersamaan
		DB::beginTransaction();
		try {
			// 2. Buat "Surat Pengantar" (entri di tabel documents)
			$document = Document::create([
				'package_id' => $package->id,
				'project_id' => $package->project_id, // Ambil dari relasi
				'user_id' => Auth::id(),
				'document_number' => $validated['document_number'],
				'status' => 'pending',
				'category' => 'shop_drawing',
				'title' => 'Shop Drawing: ' . $validated['document_number'],
				'name' => 'Shop Drawing: ' . $validated['document_number'],
				'requires_approval' => true, // Shop drawing selalu butuh persetujuan
			]);

			// 3. Simpan setiap file yang diunggah
			foreach ($request->file('files') as $file) {
				$path = $file->store('documents', 'public');
				$document->files()->create([
					'file_path' => $path,
					'original_filename' => $file->getClientOriginalName(),
				]);
			}

			// 4. Simpan SEMUA detail gambar yang diinput
			foreach ($validated['drawings'] as $drawingData) {
				$document->drawingDetails()->create([
					'drawing_number' => $drawingData['number'],
					'drawing_title' => $drawingData['title'],
					'revision' => 0,
					'status' => 'pending', // Status per gambar juga pending
				]);
			}

			// 5. Hubungkan item pekerjaan ke DOKUMEN UTAMA beserta status kelengkapannya
			if (!empty($validated['rab_items'])) {
				$rabSyncData = [];
				foreach ($validated['rab_items'] as $rabId => $details) {
					$rabSyncData[$rabId] = ['completion_status' => $details['completion_status']];
				}
				$document->rabItems()->sync($rabSyncData);
			}

			// 6. Buat catatan riwayat pengajuan awal
			$document->approvals()->create([
				'user_id' => Auth::id(),
				'status' => 'pending',
				'notes' => 'Dokumen diajukan pertama kali oleh Kontraktor.'
			]);

			DB::commit(); // Konfirmasi semua perubahan jika tidak ada error

			// 7. Arahkan kembali ke halaman utama dengan pesan sukses
			return redirect()->route('documents.index', ['package' => $package->id])
							->with('success', 'Shop Drawing berhasil diajukan dan sedang menunggu review.');

		} catch (\Exception $e) {
			DB::rollBack(); // Batalkan semua jika ada error
			// Tampilkan error untuk debugging
			return back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())->withInput();
		}
	}
	
	
	
	/**
     * Menyimpan hasil review dari MK.
     */
    public function storeReview(Request $request, Package $package, Document $shop_drawing)
    {
        // Gunakan $shop_drawing agar cocok dengan route
        $this->authorize('review', $shop_drawing);

        $validated = $request->validate([
            'drawings' => 'required|array',
            'drawings.*.status' => 'required|string|in:approved,revision,rejected',
            'drawings.*.notes' => 'nullable|string',
            
            'rab_items' => 'nullable|array',
            'rab_items.*.completion_status' => 'required|string|in:lengkap,belum_lengkap',
            
            'overall_notes' => 'nullable|string',
            'disposition' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            // Update status dan catatan untuk setiap gambar (DrawingDetail)
            foreach ($validated['drawings'] as $id => $data) {
                $drawingDetail = \App\Models\DrawingDetail::find($id);
                // Pastikan drawing detail ini milik dokumen yang benar
                if ($drawingDetail && $drawingDetail->document_id == $shop_drawing->id) {
                    $drawingDetail->update([
                        'status' => $data['status'],
                        'notes' => $data['notes'],
                        'reviewed_by' => Auth::id(),
                        'review_date' => now(),
                    ]);
                }
            }

            // Update status kelengkapan item pekerjaan di tabel pivot
            if (!empty($validated['rab_items'])) {
                $rabSyncData = [];
                foreach ($validated['rab_items'] as $rabId => $details) {
                    $rabSyncData[$rabId] = ['completion_status' => $details['completion_status']];
                }
                $shop_drawing->rabItems()->syncWithoutDetaching($rabSyncData);
            }

            // Buat catatan riwayat baru untuk proses review ini
            $shop_drawing->approvals()->create([
                'user_id' => Auth::id(),
                'status' => $validated['disposition'],
                'notes' => $validated['overall_notes'],
            ]);

            // Hitung ulang dan update status keseluruhan dari surat pengantar (Document)
            $shop_drawing->updateOverallStatus();

            // =======================================================
            // AWAL DARI BLOK LOGIKA DISPOSISI (BARU)
            // =======================================================
            // Setelah status per gambar dihitung, kita proses disposisinya.
            // Hanya proses disposisi jika status keseluruhan adalah 'approved' oleh MK.
            $disposition = $validated['disposition'];

            // Jika MK meneruskan ke Owner
            if ($shop_drawing->status === 'approved' && $disposition === 'to_owner') {
                $shop_drawing->status = 'menunggu_persetujuan_owner';
                $shop_drawing->save();
            }

            // Jika Owner membuat keputusan final
            if ($disposition === 'owner_approved') {
                $shop_drawing->status = 'approved'; // Status final: approved
                $shop_drawing->save();
            } elseif ($disposition === 'owner_rejected') {
                $shop_drawing->status = 'rejected'; // Status final: rejected
                $shop_drawing->save();
            }
            // =======================================================
            // AKHIR DARI BLOK LOGIKA DISPOSISI
            // =======================================================

            DB::commit();

            return redirect()->route('documents.index', ['package' => $package->id])
                            ->with('success', 'Hasil review berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan review: ' . $e->getMessage())->withInput();
        }
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