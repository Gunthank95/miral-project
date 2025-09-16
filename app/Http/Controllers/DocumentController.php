<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Document;
use App\Models\RabItem; // <-- KESALAHAN UTAMA DI SINI, BARIS INI HILANG
use App\Models\DrawingDetail;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\ShopDrawingStatusUpdated;
use Illuminate\Support\Facades\Notification;

class DocumentController extends Controller
{
    // GANTI method index() di dalam DocumentController

	public function index(Request $request, Package $package)
    {
        // Bagian ini dipertahankan dari kode lama Anda
        $categories = ['shop_drawing' => 'Shop Drawing'];
        $activeCategory = $request->input('category', 'shop_drawing');
        
        // ================================================================= //
        // ==================== PERBAIKAN UTAMA DI SINI ==================== //
        // ================================================================= //

        // 1. Otorisasi disesuaikan untuk menggunakan $package->project
        $this->authorize('viewAny', [Document::class, $package->project]);

        // 2. Query dasar disesuaikan dengan kode lama Anda yang menggunakan 'package_id'
        $query = \App\Models\Document::where('package_id', $package->id)
            ->with(['rabItems', 'user', 'approvals.user', 'files', 'drawingDetails']);

        // 3. Logika filter khusus untuk Owner DIHAPUS sesuai permintaan Anda
        //    agar Owner bisa melihat semua dokumen untuk keperluan laporan dan riwayat.
        //    Sekarang semua peran akan melihat daftar dokumen yang sama.
        
        // 4. Pengambilan data dan pengelompokan dipertahankan
        $allDocuments = $query->latest()->get();

        $documentsByCategory = $allDocuments->groupBy(function ($item, $key) {
            return str_replace(' ', '_', strtolower($item->category));
        });
        
        // 5. Variabel yang dikirim ke view disesuaikan kembali dengan kode lama Anda
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

        // Ambil HANYA item RAB utama (yang tidak punya parent) untuk dropdown pertama.
        $mainRabItems = RabItem::where('package_id', $package->id)
            ->whereNull('parent_id')
            ->orderBy('id', 'asc')
            ->get();

        // Kirim data tersebut ke view.
        return view('documents.create_submission', compact('package', 'mainRabItems'));
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
     * Menampilkan form untuk mengajukan revisi shop drawing.
     * Data dari dokumen asli akan di-prefill.
     */
    public function createRevisionForm(Package $package, Document $document)
    {
        // Ganti nama variabel agar lebih jelas dan konsisten dengan route model binding
        $shop_drawing = $document;
        
        // Pastikan pengguna diizinkan untuk mengajukan revisi
        $this->authorize('resubmit', $shop_drawing);

        // Muat detail gambar dan item RAB dari dokumen asli
        $shop_drawing->load('drawingDetails', 'rabItems');

        return view('documents.create_revision', compact('package', 'shop_drawing'));
    }

    /**
     * Menyimpan pengajuan revisi shop drawing baru.
     */
    public function storeRevision(Request $request, Package $package, Document $parent_document)
    {
        $this->authorize('resubmit', $parent_document);

        $validated = $request->validate([
            'document_number' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'category' => 'required|string|in:shop_drawing',
            'files.*' => 'nullable|file|mimes:pdf|max:10240', // Max 10MB per file
            'drawings' => 'required|array|min:1',
            'drawings.*.number' => 'required|string|max:255',
            'drawings.*.title' => 'required|string|max:255',
            'rab_items' => 'nullable|array',
            // Perhatikan format validasi untuk array asosiatif
            'rab_items.*.id' => 'required_with:rab_items|exists:rab_items,id',
            'rab_items.*.completion_status' => 'required_with:rab_items|string|in:lengkap,belum_lengkap',
        ]);

        DB::beginTransaction();
        try {
            // Buat dokumen revisi baru
            $newDocument = $package->documents()->create([
                'user_id' => Auth::id(),
                'document_number' => $validated['document_number'],
                'title' => $validated['title'],
                'category' => $validated['category'],
                'status' => 'pending', // Revisi selalu dimulai dengan status pending
                'parent_document_id' => $parent_document->id, // Ini menandakan sebagai revisi
                'requires_approval' => true,
                'revision' => $parent_document->revision + 1, // Naikkan nomor revisi
            ]);

            // Simpan detail gambar
            foreach ($validated['drawings'] as $drawingData) {
                $newDocument->drawingDetails()->create([
                    'drawing_number' => $drawingData['number'],
                    'drawing_title' => $drawingData['title'],
                    'status' => 'pending',
                ]);
            }

            // Simpan file yang diunggah
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('documents', 'public'); 
                    $newDocument->files()->create([
                        'original_filename' => $file->getClientOriginalName(),
                        'file_path' => $path,
                    ]);
                }
            }

            // Sinkronkan item RAB
            if (!empty($validated['rab_items'])) {
                $rabSyncData = [];
                foreach ($validated['rab_items'] as $rabItemData) {
                    // Pastikan ID ada sebelum mencoba menyinkronkan
                    if(isset($rabItemData['id'])) {
                       $rabSyncData[$rabItemData['id']] = ['completion_status' => $rabItemData['completion_status']];
                    }
                }
                $newDocument->rabItems()->sync($rabSyncData);
            }
            
            // Buat catatan riwayat
            $newDocument->approvals()->create([
                'user_id' => Auth::id(),
                'status' => 'pending',
                'notes' => 'Mengajukan Revisi Ke-' . $newDocument->revision
            ]);

            DB::commit();

            return redirect()->route('documents.index', ['package' => $package->id])
                             ->with('success', 'Revisi Shop Drawing berhasil diajukan.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Gagal mengajukan revisi shop drawing: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengajukan revisi: ' . $e->getMessage())->withInput();
        }
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

        $validated = $request->validate([
            'document_number' => 'required|string|max:255',
            'title' => 'required|string|max:255', // Menambahkan validasi untuk title
            'files.*' => 'required|file|mimes:pdf|max:10240',
            'drawings' => 'required|array|min:1',
            'drawings.*.number' => 'required|string|max:255',
            'drawings.*.title' => 'required|string|max:255',
            'rab_items' => 'nullable|array',
            'rab_items.*.id' => 'required_with:rab_items|exists:rab_items,id',
            'rab_items.*.completion_status' => 'required_with:rab_items|string|in:lengkap,belum_lengkap',
        ]);

        DB::beginTransaction();
        try {
            $document = $package->documents()->create([
                'user_id' => Auth::id(),
                'document_number' => $validated['document_number'],
                'title' => $validated['title'],
                'category' => 'shop_drawing',
                'status' => 'pending',
                'requires_approval' => true,
            ]);

            foreach ($validated['drawings'] as $drawingData) {
                $document->drawingDetails()->create([
                    'drawing_number' => $drawingData['number'],
                    'drawing_title' => $drawingData['title'],
                    'status' => 'pending',
                ]);
            }

            foreach ($request->file('files') as $file) {
                $path = $file->store('documents', 'public');
                $document->files()->create([
                    'original_filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                ]);
            }

            if (!empty($validated['rab_items'])) {
                $rabSyncData = [];
                foreach ($validated['rab_items'] as $rabItemData) {
                    $rabSyncData[$rabItemData['id']] = ['completion_status' => $rabItemData['completion_status']];
                }
                $document->rabItems()->sync($rabSyncData);
            }

            $document->approvals()->create(['user_id' => Auth::id(), 'status' => 'submitted', 'notes' => 'Dokumen diajukan.']);

            // KIRIM NOTIFIKASI KE MK
            $project = $package->project;
            $recipients = $project->getMkUsers();

            if ($recipients && !$recipients->isEmpty()) {
                // Tambahkan pesan dinamis untuk notifikasi baru
                $document->status = 'pending'; // Set status eksplisit untuk pesan notifikasi
                $notification = new ShopDrawingStatusUpdated($document);
                $notification->message = "Shop Drawing baru '{$document->title}' telah diajukan dan menunggu review.";
                Notification::send($recipients, $notification);
            }

            DB::commit();

            return redirect()->route('documents.index', ['package' => $package->id])->with('success', 'Shop Drawing berhasil diajukan dan notifikasi telah dikirim ke MK.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal mengajukan shop drawing: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengajukan shop drawing: ' . $e->getMessage())->withInput();
        }
    }
	
	
	
	/**
     * Menyimpan hasil review dari MK.
     */
    public function storeReview(Request $request, Package $package, Document $shop_drawing)
	{
		$this->authorize('review', $shop_drawing);

		$validated = $request->validate([
			'drawings' => 'required|array',
			'drawings.*.status' => 'required|string|in:approved,revision,rejected',
			'drawings.*.notes' => 'nullable|string',
			'rab_items' => 'nullable|array',
			'rab_items.*.completion_status' => 'required_with:rab_items|string|in:lengkap,belum_lengkap',
			'overall_notes' => 'nullable|string',
			'disposition' => 'required|string',
		]);

		DB::beginTransaction();
		try {
			$user = Auth::user();
			$disposition = $validated['disposition'];
			$newStatus = $shop_drawing->status; // Status awal

			// Tentukan status dokumen baru berdasarkan disposisi
			if ($disposition === 'to_owner') {
				$newStatus = 'menunggu_persetujuan_owner';
			} elseif ($disposition === 'owner_approved') {
				$newStatus = 'approved';
			} elseif ($disposition === 'owner_rejected') {
				$newStatus = 'revision'; // Atau 'rejected', sesuaikan dengan alur Anda
			}
			
			// Update status dokumen utama
			$shop_drawing->status = $newStatus;
			$shop_drawing->save();

			// Update status setiap detail gambar
			foreach ($validated['drawings'] as $id => $data) {
				DrawingDetail::where('id', $id)->update([
					'status' => $data['status'],
					'notes' => $data['notes'],
				]);
			}
			
			// Update status item RAB jika ada
			if (isset($validated['rab_items'])) {
				foreach ($validated['rab_items'] as $id => $data) {
					$shop_drawing->rabItems()->updateExistingPivot($id, [
						'completion_status' => $data['completion_status']
					]);
				}
			}
			
			// Buat log di tabel riwayat (approvals)
			$shop_drawing->approvals()->create([
				'user_id' => $user->id,
				'status' => $newStatus,
				'notes' => $validated['overall_notes'] ?? 'Review ' . $user->name,
			]);

			// ==========================================================
			// == AWAL DARI LOGIKA PENGIRIMAN NOTIFIKASI (BAGIAN BARU) ==
			// ==========================================================
			
			$recipients = null;
			
			// Skenario 1: Review MK selesai, teruskan ke Owner
			if ($newStatus === 'menunggu_persetujuan_owner') {
				$project = $package->project;
				$recipients = $project->getOwnerUsers();
			} 
			// Skenario 2: Owner sudah setuju atau menolak, beritahu Kontraktor
			else if (in_array($newStatus, ['approved', 'revision', 'rejected'])) {
				$recipients = $shop_drawing->user; // Pengguna yang mengunggah dokumen
			}
			
			// Kirim notifikasi jika ada penerima yang ditemukan
			if ($recipients && !$recipients->isEmpty()) {
				Notification::send($recipients, new ShopDrawingStatusUpdated($shop_drawing));
			}

			// ========================================================
			// == AKHIR DARI LOGIKA PENGIRIMAN NOTIFIKASI ==
			// ========================================================

			DB::commit();

			return redirect()->route('documents.index', ['package' => $package->id])
							 ->with('success', 'Review berhasil disimpan.');

		} catch (\Exception $e) {
			DB::rollBack();
			Log::error("Gagal menyimpan review: " . $e->getMessage());
			return back()->with('error', 'Terjadi kesalahan saat menyimpan review.');
		}
	}
	
	public function destroy(Package $package, Document $shop_drawing)
	{
		// 1. Otorisasi: Variabel $document diubah menjadi $shop_drawing
		$this->authorize('delete', $shop_drawing);

		// 2. Gunakan Transaksi Database untuk keamanan
		DB::beginTransaction();
		try {
			// 3. Hapus SEMUA file fisik dari storage
			if ($shop_drawing->files->isNotEmpty()) {
				foreach ($shop_drawing->files as $file) {
					Storage::disk('public')->delete($file->file_path);
				}
			}

			// 4. Hapus record dokumen dari database
			$shop_drawing->delete();

			DB::commit();
			
			return redirect()->route('documents.index', $package)
							 ->with('success', 'Dokumen pengajuan berhasil dihapus.');

		} catch (\Exception $e) {
			DB::rollBack();
			Log::error('Gagal menghapus dokumen: ' . $e->getMessage());
			return redirect()->route('documents.index', $package)
							 ->with('error', 'Gagal menghapus dokumen.');
		}
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
	
	// Helper functions (buildTree & flattenTreeForDropdown)
    private function buildTree($items, $parentId = null) {
        $branch = collect();
        foreach ($items->where('parent_id', $parentId) as $item) {
            $item->children = $this->buildTree($items, $item->id);
            $branch->push($item);
        }
        return $branch;
    }
    private function flattenTreeForDropdown($items, $level = 0) {
        $options = [];
        foreach ($items as $item) {
            $prefix = str_repeat('&nbsp;&nbsp;&nbsp;', $level);
            $options[] = [ 'id' => $item->id, 'name' => $prefix . $item->item_number . ' ' . $item->item_name, 'disabled' => $item->disabled ];
            if ($item->children->isNotEmpty()) {
                $options = array_merge($options, $this->flattenTreeForDropdown($item->children, $level + 1));
            }
        }
        return $options;
    }
}