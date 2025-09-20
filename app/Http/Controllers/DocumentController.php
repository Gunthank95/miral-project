<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Package;
use App\Models\Document;
use App\Models\RabItem; // <-- KESALAHAN UTAMA DI SINI, BARIS INI HILANG
use App\Models\DrawingDetail;
use App\Models\Project;
use App\Models\DocumentApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\ShopDrawingStatusUpdated;
use Illuminate\Support\Facades\Notification;
use App\Models\DocumentInternalReview;

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
			->with([
				'user', 
				'rabItems', 
				'approvals.user', 
				'files', 
				'drawingDetails',
				'internalReviews.user' // <-- TAMBAHAN: Ambil semua review internal & data penggunanya
			]);

        // 3. Logika filter khusus untuk Owner DIHAPUS sesuai permintaan Anda
        //    agar Owner bisa melihat semua dokumen untuk keperluan laporan dan riwayat.
        //    Sekarang semua peran akan melihat daftar dokumen yang sama.
        
        // 4. Pengambilan data dan pengelompokan dipertahankan
        $allDocuments = $query->latest()->get();

        $documentsByCategory = $allDocuments->groupBy(function ($item, $key) {
            return str_replace(' ', '_', strtolower($item->category));
        });
        
        // 5. Variabel yang dikirim ke view disesuaikan kembali dengan kode lama Anda
        $mkTeamLevels = config('roles.mk');
		$mkTeamMembers = User::whereHas('projectRoles', function ($query) use ($package, $mkTeamLevels) {
			$query->where('project_id', $package->project_id)
				  ->whereIn('role_level', $mkTeamLevels);
		})->orderBy('name')->get();

		// Sesuaikan baris return view untuk mengirim data baru
		return view('documents.index', compact('package', 'categories', 'activeCategory', 'documentsByCategory', 'mkTeamMembers'));
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
        $this->authorize('create', [Document::class, $package->project]);

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
        $this->authorize('create', [Document::class, $package->project]);

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
		// Otorisasi: Pastikan pengguna punya hak untuk mereview atau mengedit review
		if ($request->user()->cannot('review', $shop_drawing) && $request->user()->cannot('editReview', 'shop_drawing')) {
			abort(403);
		}

		// Validasi input dari form
		$validated = $request->validate([
			'drawings' => 'required|array',
			'drawings.*.status' => 'required|in:approved,revision,rejected',
			'drawings.*.notes' => 'nullable|string|max:255',
			'overall_notes' => 'nullable|string|max:1000',
			'disposition' => 'nullable|string|in:to_owner,to_revision', // Disposisi kini opsional
		]);

		$user = Auth::user();
		$project = $package->project;

		// Ambil level jabatan pengguna di proyek ini
		$userLevel = $user->getLevelInProject($project->id);
		$isPM = ($userLevel == 60); // Asumsi PM adalah level 60

		DB::beginTransaction();
		try {
			// --- LOGIKA UNTUK PROJECT MANAGER (PENGAMBIL KEPUTUSAN FINAL MK) ---
			if ($isPM) {
				$disposition = $validated['disposition'] ?? null;
				if (!$disposition) {
					return redirect()->back()->with('error', 'Sebagai Project Manager, Anda harus memilih disposisi akhir.');
				}

				$newStatus = ($disposition === 'to_owner') ? 'menunggu_persetujuan_owner' : 'revision';
				$shop_drawing->update(['status' => $newStatus]);

				// Buat log persetujuan utama
				DocumentApproval::create([
					'document_id' => $shop_drawing->id,
					'user_id' => $user->id,
					'status' => $newStatus,
					'notes' => $validated['overall_notes'],
				]);
				
				// Kirim notifikasi
				if ($newStatus === 'menunggu_persetujuan_owner') {
					$this->notifyUsers($project, $shop_drawing, ['owner']);
				} else {
					$this->notifyUsers($project, $shop_drawing, ['contractor']);
				}

			// --- LOGIKA UNTUK ENGINEER/REVIEWER INTERNAL LAINNYA ---
			} else {
				// Cek apakah ada gambar yang butuh revisi
				$needsRevision = collect($validated['drawings'])->contains('status', 'revision');
				
				// Simpan atau perbarui review di tabel internal
				DocumentInternalReview::updateOrCreate(
					[
						'document_id' => $shop_drawing->id,
						'user_id' => $user->id,
					],
					[
						'status' => $needsRevision ? 'revision_needed' : 'reviewed',
						'notes' => $validated['overall_notes'],
						'drawing_reviews' => $validated['drawings'], // Simpan detail review per gambar
					]
				);
				// Catatan: Status dokumen utama TIDAK berubah
			}

			DB::commit();
			return redirect()->route('documents.index', $package)->with('success', 'Hasil review Anda berhasil disimpan.');

		} catch (\Exception $e) {
			DB::rollBack();
			Log::error('Error saat menyimpan review: ' . $e->getMessage());
			return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan review.');
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
	
	/**
     * Helper function to send notifications to relevant users.
     *
     * @param \App\Models\Project $project
     * @param \App\Models\Document $document
     * @param array $rolesToNotify
     * @return void
     */
    private function notifyUsers(Project $project, Document $document, array $rolesToNotify)
    {
        // Jika notifikasi untuk kontraktor
        if (in_array('contractor', $rolesToNotify)) {
            $contractor = $document->user; // Ambil user yang membuat dokumen
            if ($contractor) {
                Notification::send($contractor, new ShopDrawingStatusUpdated($document, $project));
            }
            // Hapus 'contractor' dari array agar tidak diproses lagi di bawah
            $rolesToNotify = array_diff($rolesToNotify, ['contractor']);
        }

        if (empty($rolesToNotify)) {
            return; // Keluar jika tidak ada peran lain yang perlu dinotifikasi
        }

        $levels = [];
        if (in_array('mk', $rolesToNotify)) {
			$mk_levels = config('roles.mk');
			if (is_array($mk_levels)) {
				$levels = array_merge($levels, $mk_levels);
			}
		}

		// Cek dulu apakah konfigurasi 'owner' ada sebelum digabungkan
		if (in_array('owner', $rolesToNotify)) {
			$owner_levels = config('roles.owner');
			if (is_array($owner_levels)) {
				$levels = array_merge($levels, $owner_levels);
			}
		}
        
        if (empty($levels)) {
            return;
        }

        // Cari semua user dengan level peran yang sesuai di proyek ini
        $usersToNotify = User::whereHas('projectRoles', function ($query) use ($project, $levels) {
            $query->where('project_id', $project->id)
                  ->whereIn('role_level', $levels);
        })->get();

        if ($usersToNotify->isNotEmpty()) {
            Notification::send($usersToNotify, new ShopDrawingStatusUpdated($document, $project));
        }
    }
}