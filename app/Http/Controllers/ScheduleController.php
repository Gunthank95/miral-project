<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\RabItem;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index(Package $package)
    {
        $tasks_from_db = Schedule::where('package_id', $package->id)->orderBy('sort_order')->get();
        $tasks = $tasks_from_db->map(function ($task) {
            $startDate = Carbon::parse($task->start_date);
            $endDate = Carbon::parse($task->end_date);
            // Perhitungan durasi harusnya tidak ditambah 1 jika start & end sama
            $duration = $startDate->diffInDays($endDate);

            return [
                'id' => $task->id,
                'text' => $task->task_name,
                'start_date' => $startDate->format('d-m-Y'),
                'duration' => $duration > 0 ? $duration : 1, // Durasi minimal 1 hari
                'progress' => $task->progress / 100,
                'parent' => (int) $task->parent_id,
                'open' => true,
            ];
        });

        return view('schedules.index', [
            'package' => $package,
            'tasks_data' => json_encode(["data" => $tasks, "links" => []])
        ]);
    }

    public function store(Request $request, Package $package)
    {
        $validated = $request->validate([
            'task_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        DB::transaction(function () use ($package, $validated) {
            $maxOrder = $package->schedules()->max('sort_order');
            $schedule = $package->schedules()->create([
                'task_name' => $validated['task_name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'sort_order' => $maxOrder + 1,
            ]);

            if ($schedule->parent_id) $this->updateParentDates($schedule->parent_id);
        });

        return redirect()->route('schedules.index', $package->id)->with('success', 'Tugas baru berhasil ditambahkan.');
    }

    // GANTI: Logika importFromRab diubah untuk "update or create"
    public function importFromRab(Request $request, Package $package)
    {
        DB::transaction(function () use ($package) {
            $rabItems = RabItem::where('package_id', $package->id)->orderBy('id')->get();
            if ($rabItems->isEmpty()) return;

            $rabIdToScheduleIdMap = [];
            $sortOrder = 0;

            foreach ($rabItems as $item) {
                // Gunakan updateOrCreate untuk menghindari duplikasi
                $schedule = Schedule::updateOrCreate(
                    ['package_id' => $package->id, 'rab_item_id' => $item->id],
                    [
                        'task_name' => $item->item_number . ' ' . $item->item_name,
                        'start_date' => now(), 'end_date' => now(), 'progress' => 0,
                        'sort_order' => $sortOrder++,
                    ]
                );
                $rabIdToScheduleIdMap[$item->id] = $schedule->id;
            }

            foreach ($rabItems as $item) {
                if ($item->parent_id && isset($rabIdToScheduleIdMap[$item->id], $rabIdToScheduleIdMap[$item->parent_id])) {
                    Schedule::where('id', $rabIdToScheduleIdMap[$item->id])
                            ->update(['parent_id' => $rabIdToScheduleIdMap[$item->parent_id]]);
                }
            }
        });

        return redirect()->route('schedules.index', $package->id)->with('success', 'Jadwal berhasil disinkronkan dari RAB.');
    }

    // GANTI: Fungsi update diubah untuk memperbarui parent
    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'task_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        
        DB::transaction(function () use ($schedule, $validated) {
            $schedule->update($validated);
            if ($schedule->parent_id) $this->updateParentDates($schedule->parent_id);
        });

        return redirect()->back()->with('success', 'Tugas berhasil diperbarui.');
    }

    public function destroy(Schedule $schedule)
    {
        DB::transaction(function () use ($schedule) {
            $parentId = $schedule->parent_id;
            $childIds = $this->getAllChildIds($schedule->id);
            Schedule::whereIn('id', $childIds)->delete();
            $schedule->delete();
            if ($parentId) $this->updateParentDates($parentId);
        });
        return response()->json(['status' => 'success']);
    }
    
    private function getAllChildIds($parentId) {
        $ids = [];
        $children = Schedule::where('parent_id', $parentId)->get();
        foreach ($children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getAllChildIds($child->id));
        }
        return $ids;
    }

    public function batchDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:schedules,id']);
        DB::transaction(function () use ($request) {
            $allIdsToDelete = [];
            foreach ($request->ids as $id) {
                $allIdsToDelete = array_merge($allIdsToDelete, $this->getAllChildIds($id));
            }
            Schedule::destroy(array_unique(array_merge($request->ids, $allIdsToDelete)));
        });
        return response()->json(['status' => 'success']);
    }

    public function updateOrder(Request $request)
    {
        $request->validate(['order' => 'required|array', 'order.*.id' => 'required|integer|exists:schedules,id']);
        DB::transaction(function () use ($request) {
            foreach ($request->order as $index => $taskData) {
                Schedule::find($taskData['id'])->update(['sort_order' => $index, 'parent_id' => $taskData['parent']]);
            }
        });
        return response()->json(['status' => 'success']);
    }
    
    // TAMBAHKAN: Fungsi rekursif baru untuk update tanggal parent
    private function updateParentDates($parentId)
    {
        if (!$parent = Schedule::find($parentId)) return;
        $children = Schedule::where('parent_id', $parentId)->get();
        if ($children->isNotEmpty()) {
            $parent->update(['start_date' => $children->min('start_date'), 'end_date' => $children->max('end_date')]);
            if ($parent->parent_id) $this->updateParentDates($parent->parent_id);
        }
    }
	
	public function getScheduleData(Package $package)
    {
        $tasks_from_db = Schedule::where('package_id', $package->id)->orderBy('sort_order')->get();

        $tasks = $tasks_from_db->map(function ($task) {
            $startDate = Carbon::parse($task->start_date);
            $endDate = Carbon::parse($task->end_date);
            $duration = $startDate->diffInDays($endDate);

            return [
                'id' => $task->id,
                'text' => $task->task_name,
                'start_date' => $startDate->format('Y-m-d'), // Format untuk gantt.load
                'duration' => $duration > 0 ? $duration : 1,
                'progress' => $task->progress / 100,
                'parent' => (int) $task->parent_id,
                'open' => true,
            ];
        });

        // DHTMLX Gantt API load() mengharapkan format ini
        return response()->json([
            "data" => $tasks,
            "links" => []
        ]);
    }
}