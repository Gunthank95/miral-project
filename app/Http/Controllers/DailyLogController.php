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

    // GANTI: Gunakan UpdateDailyLogRequest di sini
    public function update(UpdateDailyLogRequest $request, DailyLog $daily_log)
    {
        // GANTI: Ambil data yang sudah tervalidasi
        $validated = $request->validated();
        $daily_log->update($validated);
        
        return redirect()->route('daily_reports.edit', ['package' => $daily_log->package_id, 'daily_report' => $daily_log->daily_report_id])
                         ->with('success', 'Aktivitas berhasil diperbarui.');
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