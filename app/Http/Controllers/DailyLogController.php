<?php

namespace App\Http\Controllers;

// TAMBAHKAN: Panggil Form Request yang baru
use App\Http\Requests\StoreDailyLogRequest;
use App\Http\Requests\UpdateDailyLogRequest;
use App\Models\DailyLog;
use App\Models\DailyReport;
use Illuminate\Support\Facades\DB;

class DailyLogController extends Controller
{
    // GANTI: Gunakan StoreDailyLogRequest di sini
    public function store(StoreDailyLogRequest $request, DailyReport $daily_report)
    {
        // GANTI: Ambil data yang sudah tervalidasi
        $validated = $request->validated();
        
        DB::transaction(function () use ($daily_report, $validated, $request) {
            $logData = [
                'user_id' => auth()->id(),
                'package_id' => $daily_report->package_id,
                'rab_item_id' => $validated['rab_item_id'] ?? null,
                'custom_work_name' => $validated['custom_work_name'] ?? null,
                'progress_volume' => $validated['progress_volume'],
                'description' => $validated['description'] ?? null,
                'log_date' => $daily_report->report_date,
            ];

            $dailyLog = $daily_report->activities()->create($logData);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('daily_log_photos', 'public');
                    $dailyLog->photos()->create(['photo_path' => $path]);
                }
            }

            if (isset($validated['materials'])) {
                foreach ($validated['materials'] as $material) {
                    $dailyLog->materials()->create($material);
                }
            }

            if (isset($validated['equipment'])) {
                foreach ($validated['equipment'] as $equip) {
                    $dailyLog->equipment()->create($equip);
                }
            }
        });

        return back()->with('success', 'Aktivitas berhasil ditambahkan.');
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