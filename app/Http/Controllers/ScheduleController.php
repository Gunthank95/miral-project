<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\RabItem;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Package $package)
    {
        // GANTI: Urutkan berdasarkan sort_order
        $tasks_from_db = Schedule::where('package_id', $package->id)->orderBy('sort_order')->get();

        $tasks = $tasks_from_db->map(function ($task) {
            $startDate = \Carbon\Carbon::parse($task->start_date);
            $endDate = \Carbon\Carbon::parse($task->end_date);
            $duration = $startDate->diffInDays($endDate);

            return [
                'id' => $task->id,
                'text' => $task->task_name,
                'start_date' => $startDate->format('d-m-Y'),
                'duration' => $duration,
                'progress' => $task->progress / 100,
                'parent' => $task->parent_id,
            ];
        });

        $links = [];

        return view('schedules.index', [
            'package' => $package,
            'tasks_data' => json_encode(["data" => $tasks, "links" => $links])
        ]);
    }

    public function store(Request $request, Package $package)
    {
        $validated = $request->validate([
            'task_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $maxOrder = $package->schedules()->max('sort_order');
        $validated['sort_order'] = $maxOrder + 1;

        $package->schedules()->create($validated);

        return redirect()->route('schedule.index', $package->id)->with('success', 'Tugas baru berhasil ditambahkan.');
    }

    public function importFromRab(Request $request, Package $package)
	{
		// 1. Hapus semua tugas LAMA yang berasal dari impor RAB di paket ini
		Schedule::where('package_id', $package->id)
				->whereNotNull('rab_item_id')
				->delete();

		// 2. Ambil semua item dari RAB untuk diimpor ulang
		$rabItems = RabItem::where('package_id', $package->id)->get();

		if ($rabItems->isEmpty()) {
			return redirect()->route('schedule.index', $package->id)
				->with('success', 'Tidak ada item di RAB untuk diimpor.');
		}

		$rabIdToScheduleIdMap = [];
		$sortOrder = 0;

		// 3. Pass pertama: Buat semua jadwal baru
		foreach ($rabItems as $item) {
			$schedule = Schedule::create([
				'package_id' => $package->id,
				'rab_item_id' => $item->id,
				'task_name' => $item->item_number . ' ' . $item->item_name,
				'start_date' => now(),
				'end_date' => now(),
				'progress' => 0,
				'sort_order' => $sortOrder++,
			]);
			$rabIdToScheduleIdMap[$item->id] = $schedule->id;
		}

		// 4. Pass kedua: Update parent_id untuk mencocokkan struktur RAB
		foreach ($rabItems as $item) {
			if ($item->parent_id && isset($rabIdToScheduleIdMap[$item->id]) && isset($rabIdToScheduleIdMap[$item->parent_id])) {
				$scheduleId = $rabIdToScheduleIdMap[$item->id];
				$parentId = $rabIdToScheduleIdMap[$item->parent_id];
				Schedule::where('id', $scheduleId)->update(['parent_id' => $parentId]);
			}
		}

		return redirect()->route('schedule.index', $package->id)
			->with('success', $rabItems->count() . " tugas berhasil disinkronkan dari RAB.");
	}

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return response()->json(['status' => 'success']);
    }

    public function batchDestroy(Request $request)
    {
        $request->validate([ 'ids' => 'required|array', 'ids.*' => 'integer|exists:schedules,id']);
        Schedule::destroy($request->ids);
        return response()->json(['status' => 'success']);
    }
    
    // TAMBAHKAN FUNGSI BARU INI
    public function updateOrder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:schedules,id',
        ]);
        
        foreach ($request->order as $index => $taskId) {
            Schedule::where('id', $taskId)->update(['sort_order' => $index]);
        }

        return response()->json(['status' => 'success']);
    }
}