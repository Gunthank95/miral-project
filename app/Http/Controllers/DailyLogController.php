<?php

namespace App\Http\Controllers;

use App\Models\DailyLog;
use App\Models\DailyReport;
use App\Models\Material;
use App\Models\DailyLogMaterial;
use App\Models\DailyLogEquipment;
use App\Models\DailyLogPhoto;
use App\Models\DailyLogManpower;
use App\Models\RabItem;
use Illuminate\Http\Request; // GANTI: Pastikan namespace ini benar
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Models\Package;

class DailyLogController extends Controller
{
    public function create(DailyReport $daily_report)
    {
        $package = $daily_report->package;
        $mainRabItems = $package->rabItems()->whereNull('parent_id')->get()->sortBy('id');
        $materials = Material::orderBy('name')->get();

        return view('daily_logs.create', [
            'package' => $package,
            'report' => $daily_report,
            'mainRabItems' => $mainRabItems,
            'materials' => $materials,
        ]);
    }

    public function store(Request $request, Package $package)
	{
		try {
			$validatedData = $request->validate([
				'daily_report_id' => 'required|exists:daily_reports,id',
				'rab_item_id' => 'required_without:custom_work_name|nullable|exists:rab_items,id',
				'custom_work_name' => 'required_without:rab_item_id|nullable|string|max:255',
				'progress_volume' => 'nullable|numeric',
				'manpower' => 'nullable|array',
				'materials' => 'nullable|array',
				'equipment' => 'nullable|array',
				'photos' => 'nullable|array',
			]);

			$dailyReport = DailyReport::find($validatedData['daily_report_id']);

			$dailyLog = DailyLog::create([
				'daily_report_id' => $validatedData['daily_report_id'],
				'package_id' => $package->id,
				'rab_item_id' => $validatedData['rab_item_id'] ?? null,
				'custom_work_name' => $validatedData['custom_work_name'] ?? null,
				'user_id' => Auth::id(),
				'log_date' => $dailyReport->report_date,
				'progress_volume' => $validatedData['progress_volume'] ?? 0,
			]);

			// GANTI: Logika penyimpanan yang lengkap
			if ($request->filled('manpower')) {
				foreach ($request->manpower as $manpowerData) {
					if (!empty($manpowerData['role']) && !empty($manpowerData['quantity'])) {
						$dailyLog->manpower()->create([
							'role' => $manpowerData['role'],
							'quantity' => $manpowerData['quantity'],
						]);
					}
				}
			}

			if ($request->filled('materials')) {
				foreach ($request->materials as $materialData) {
					if (!empty($materialData['id'])) {
						DailyLogMaterial::create([
							'daily_log_id' => $dailyLog->id,
							'material_id' => $materialData['id'],
							'quantity' => $materialData['quantity'] ?? 0,
							'unit' => $materialData['unit'],
						]);
					}
				}
			}

			if ($request->filled('equipment')) {
				foreach ($request->equipment as $equipmentData) {
					if (!empty($equipmentData['name'])) {
						$dailyLog->equipment()->create([
							'name' => $equipmentData['name'],
							'quantity' => $equipmentData['quantity'] ?? 1,
							'specification' => $equipmentData['specification'],
						]);
					}
				}
			}

			if ($request->hasFile('photos')) {
				foreach ($request->file('photos') as $photo) {
					if ($photo) {
						$path = $photo->store('photos', 'public');
						DailyLogPhoto::create([
							'daily_log_id' => $dailyLog->id,
							'file_path' => $path,
						]);
					}
				}
			}

			$request->session()->flash('success', 'Aktivitas pekerjaan berhasil ditambahkan!');

			if ($request->wantsJson()) {
				return response()->json(['success' => true, 'redirect_url' => route('daily_reports.edit', ['package' => $package->id, 'daily_report' => $dailyReport->id])]);
			}
			return redirect()->route('daily_reports.edit', ['package' => $package->id, 'daily_report' => $dailyReport->id]);

		} catch (ValidationException $e) {
			if ($request->wantsJson()) {
				return response()->json([
					'success' => false,
					'message' => 'Data tidak valid. Silakan periksa kembali isian Anda.',
					'errors' => $e->errors()
				], 422);
			}
			return back()->withErrors($e->errors())->withInput();
		}
	}
	
	public function edit(DailyLog $daily_log)
    {
        // Secara otomatis, Laravel akan mencari DailyLog berdasarkan ID dari URL.
        // Kita sebut variabelnya $daily_log sesuai dengan parameter di route.

        // Ambil data terkait yang dibutuhkan untuk form
        $report = $daily_log->report;
        $package = $report->package;
        $mainRabItems = $package->rabItems()->whereNull('parent_id')->get()->sortBy('id');
        $materials = Material::orderBy('name')->get();

        // Kirim semua data yang dibutuhkan ke view 'daily_logs.edit'
        return view('daily_logs.edit', [
            'activity' => $daily_log, // View menggunakan variabel 'activity', jadi kita sesuaikan.
            'report' => $report,
            'package' => $package,
            'mainRabItems' => $mainRabItems,
            'materials' => $materials,
        ]);
    }

    // GANTI: Gunakan UpdateDailyLogRequest di sini
    public function update(Request $request, DailyLog $daily_log)
    {
        $validated = $request->validate([
            'rab_item_id' => 'required|exists:rab_items,id',
            'progress_volume' => 'required|numeric|min:0',
            'manpower_count' => 'nullable|integer',
            'new_photos.*' => 'nullable|image|max:2048',
            'deleted_photos' => 'nullable|json',
            // TAMBAHKAN: Validasi untuk material dan peralatan
            'materials' => 'nullable|array',
            'materials.*.id' => 'required_with:materials|exists:materials,id',
            'materials.*.quantity' => 'required_with:materials|numeric|min:0',
            'materials.*.unit' => 'required_with:materials|string',
            'equipment' => 'nullable|array',
            'equipment.*.name' => 'required_with:equipment|string|max:255',
            'equipment.*.quantity' => 'required_with:equipment|integer|min:1',
            'equipment.*.specification' => 'nullable|string',
        ]);

        \DB::beginTransaction();
        try {
            $daily_log->update([
                'progress_volume' => $validated['progress_volume'],
                'manpower_count' => $validated['manpower_count'] ?? $daily_log->manpower_count,
            ]);

            // TAMBAHKAN: Logika untuk update material
            // 1. Hapus semua material lama yang terkait dengan log ini
            $daily_log->materials()->delete();
            // 2. Masukkan kembali material dari form jika ada
            if (!empty($validated['materials'])) {
                foreach ($validated['materials'] as $materialData) {
                    if (!is_null($materialData['id']) && !is_null($materialData['quantity'])) {
                        $daily_log->materials()->create([
                            'material_id' => $materialData['id'],
                            'quantity' => $materialData['quantity'],
                            'unit' => $materialData['unit']
                        ]);
                    }
                }
            }

            // TAMBAHKAN: Logika untuk update peralatan
            // 1. Hapus semua peralatan lama
            $daily_log->equipment()->delete();
            // 2. Masukkan kembali peralatan dari form jika ada
            if (!empty($validated['equipment'])) {
                foreach ($validated['equipment'] as $equipmentData) {
                     if (!is_null($equipmentData['name']) && !is_null($equipmentData['quantity'])) {
                        $daily_log->equipment()->create([
                            'name' => $equipmentData['name'],
                            'quantity' => $equipmentData['quantity'],
                            'specification' => $equipmentData['specification']
                        ]);
                    }
                }
            }


            // Logika untuk proses foto (dari solusi sebelumnya, tetap dipertahankan)
            if ($request->hasFile('new_photos')) {
                foreach ($request->file('new_photos') as $photo) {
                    $path = $photo->store('photos', 'public');
                    $daily_log->photos()->create(['file_path' => $path]);
                }
            }

            if ($request->filled('deleted_photos')) {
                $deletedPhotoIds = json_decode($request->deleted_photos, true);
                if (is_array($deletedPhotoIds) && !empty($deletedPhotoIds)) {
                    $photosToDelete = $daily_log->photos()->whereIn('id', $deletedPhotoIds)->get();
                    foreach ($photosToDelete as $photo) {
                        \Storage::disk('public')->delete($photo->file_path);
                        $photo->delete();
                    }
                }
            }
            
            \DB::commit();
            
            return response()->json([
                'success' => true,
                'redirect_url' => route('daily_reports.edit', ['package' => $daily_log->report->package_id, 'daily_report' => $daily_log->daily_report_id])
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error updating daily log: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'errors' => ['general' => ['Terjadi kesalahan saat memperbarui data.']]
            ], 500);
        }
    }

    public function destroy(DailyLog $daily_log)
    {
        $package_id = $daily_log->package_id;
        $daily_report_id = $daily_log->daily_report_id;
        foreach ($daily_log->photos as $photo) {
            Storage::disk('public')->delete($photo->file_path);
        }
        $daily_log->delete();
        return redirect()->route('daily_reports.edit', ['package' => $package_id, 'daily_report' => $daily_report_id])
                         ->with('success', 'Aktivitas pekerjaan berhasil dihapus.');
    }
    
    // Helper functions
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
            $options[] = [ 'id' => $item->id, 'name' => $prefix . $item->item_number . ' ' . $item->item_name, 'is_title' => is_null($item->volume) ];
            if ($item->children->isNotEmpty()) {
                $options = array_merge($options, $this->flattenTreeForDropdown($item->children, $level + 1));
            }
        }
        return $options;
    }
}