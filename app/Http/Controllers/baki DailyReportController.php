<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\DailyLog;
use App\Models\DailyReport;
use App\Models\Material;
use App\Models\DailyLogMaterial;
use App\Models\DailyLogEquipment;
use App\Models\DailyLogPhoto;
use App\Models\RabItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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
                'rab_item_id' => 'required|exists:rab_items,id',
                'progress_volume' => 'required|numeric',
                'manpower_count' => 'nullable|integer',
                'materials' => 'nullable|array',
                'materials.*.id' => 'sometimes|required|exists:materials,id',
                'materials.*.quantity' => 'sometimes|nullable|numeric',
                'materials.*.unit' => 'sometimes|required|string',
                'equipment' => 'nullable|array',
                'equipment.*.name' => 'sometimes|required|string',
                'equipment.*.quantity' => 'sometimes|nullable|integer',
                'equipment.*.specification' => 'nullable|string',
                'photos' => 'nullable|array',
                'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'
            ]);

            $dailyReport = DailyReport::find($validatedData['daily_report_id']);

            $dailyLog = DailyLog::create([
                'daily_report_id' => $validatedData['daily_report_id'],
                'package_id' => $package->id,
                'rab_item_id' => $validatedData['rab_item_id'],
                'user_id' => Auth::id(),
                'log_date' => $dailyReport->report_date,
                'progress_volume' => $validatedData['progress_volume'],
                'manpower_count' => $validatedData['manpower_count'] ?? null,
            ]);

            // ======================================================
            // LOGIKA PENYIMPANAN MATERIAL YANG HILANG
            // ======================================================
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

            // ======================================================
            // LOGIKA PENYIMPANAN PERALATAN
            // ======================================================
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

            // ======================================================
            // LOGIKA PENYIMPANAN FOTO
            // ======================================================
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

    /**
     * FUNGSI EDIT YANG DIPERBARUI
     * Sekarang mengirimkan semua data yang dibutuhkan untuk form canggih.
     */
    public function edit(DailyLog $daily_log)
    {
        $report = $daily_log->report;
        $package = $report->package;
        $materials = Material::orderBy('name')->get();

        // Siapkan data untuk dropdown bertingkat
        $mainRabItems = $package->rabItems()->whereNull('parent_id')->get()->sortBy('id');
        
        $rabOptions = collect();
        if ($daily_log->rabItem && $daily_log->rabItem->parent_id) {
            $rabOptions = $package->rabItems()->where('parent_id', $daily_log->rabItem->parent_id)->get()->sortBy('id');
        } else if ($daily_log->rabItem) {
            $rabOptions = collect([$daily_log->rabItem]);
        }

        $daily_log->load('materials.material', 'equipment', 'photos', 'rabItem.parent');

        return view('daily_logs.edit', [
            'package' => $package,
            'report' => $report,
            'activity' => $daily_log,
            'mainRabItems' => $mainRabItems,
            'rabOptions' => $rabOptions,
            'materials' => $materials,
        ]);
    }

    /**
     * FUNGSI UPDATE YANG DIPERBARUI
     * Sekarang menangani semua jenis data dinamis.
     */
    public function update(Request $request, DailyLog $daily_log)
    {
        $validatedData = $request->validate([
            'rab_item_id' => 'required|exists:rab_items,id',
            'progress_volume' => 'required|numeric',
            'manpower_count' => 'nullable|integer',
            'materials' => 'nullable|array',
            'equipment' => 'nullable|array',
            'photos' => 'nullable|array',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'deleted_photos' => 'nullable|string',
        ]);

        // 1. Update data utama
        $daily_log->update($validatedData);

        // 2. Sinkronisasi Material
        $daily_log->materials()->delete();
        if ($request->filled('materials')) {
            foreach ($request->materials as $materialData) {
                if (!empty($materialData['id'])) {
                    DailyLogMaterial::create([
                        'daily_log_id' => $daily_log->id,
                        'material_id' => $materialData['id'],
                        'quantity' => $materialData['quantity'] ?? 0,
                        'unit' => $materialData['unit'],
                    ]);
                }
            }
        }

        // 3. Sinkronisasi Peralatan
        $daily_log->equipment()->delete();
        if ($request->filled('equipment')) {
            foreach ($request->equipment as $equipmentData) {
                if (!empty($equipmentData['name'])) {
                    DailyLogEquipment::create([
                        'daily_log_id' => $daily_log->id,
                        'name' => $equipmentData['name'],
                        'quantity' => $equipmentData['quantity'] ?? 1,
                        'specification' => $equipmentData['specification'],
                    ]);
                }
            }
        }
        
        // 4. Hapus Foto Lama
        if ($request->filled('deleted_photos')) {
            $deletedPhotoIds = explode(',', $request->deleted_photos);
            $photosToDelete = DailyLogPhoto::whereIn('id', $deletedPhotoIds)->get();
            foreach($photosToDelete as $photo) {
                Storage::disk('public')->delete($photo->file_path);
                $photo->delete();
            }
        }

        // 5. Tambah Foto Baru
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                if ($photo) {
                    $path = $photo->store('photos', 'public');
                    DailyLogPhoto::create([
                        'daily_log_id' => $daily_log->id,
                        'file_path' => $path,
                    ]);
                }
            }
        }

        $request->session()->flash('success', 'Aktivitas pekerjaan berhasil diperbarui!');
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'redirect_url' => route('daily_reports.edit', ['package' => $daily_log->package_id, 'daily_report' => $daily_log->daily_report_id])]);
        }
        return redirect()->route('daily_reports.edit', ['package' => $daily_log->package_id, 'daily_report' => $daily_log->daily_report_id]);
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