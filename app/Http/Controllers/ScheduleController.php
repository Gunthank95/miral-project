<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\RabItem;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    public function index(Package $package)
    {
        $tasks_from_db = Schedule::where('package_id', $package->id)->orderBy('sort_order')->get();

        $tasks = $tasks_from_db->map(function ($task) {
            $startDate = \Carbon\Carbon::parse($task->start_date);
            $endDate = \Carbon\Carbon::parse($task->end_date);
            $duration = $startDate->diffInDays($endDate) + 1;

            return [
                'id' => $task->id,
                'text' => $task->task_name,
                'start_date' => $startDate->format('d-m-Y'),
                'duration' => $duration,
                'progress' => $task->progress / 100,
                'parent' => (int) $task->parent_id,
                'open' => true,
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

        return redirect()->route('schedule.index', $package->id)->with('success', 'New task added successfully.');
    }

    public function importFromRab(Request $request, Package $package)
    {
        DB::transaction(function () use ($package) {
            Schedule::where('package_id', $package->id)->whereNotNull('rab_item_id')->delete();
            $rabItems = RabItem::where('package_id', $package->id)->orderBy('id')->get();
            if ($rabItems->isEmpty()) { return; }

            $rabIdToScheduleIdMap = []; $sortOrder = 0;
            foreach ($rabItems as $item) {
                $schedule = Schedule::create([
                    'package_id' => $package->id, 'rab_item_id' => $item->id,
                    'task_name' => $item->item_number . ' ' . $item->item_name,
                    'start_date' => now(), 'end_date' => now(), 'progress' => 0,
                    'sort_order' => $sortOrder++,
                ]);
                $rabIdToScheduleIdMap[$item->id] = $schedule->id;
            }
            foreach ($rabItems as $item) {
                if ($item->parent_id && isset($rabIdToScheduleIdMap[$item->id]) && isset($rabIdToScheduleIdMap[$item->parent_id])) {
                    Schedule::where('id', $rabIdToScheduleIdMap[$item->id])
                            ->update(['parent_id' => $rabIdToScheduleIdMap[$item->parent_id]]);
                }
            }
        });
        return redirect()->route('schedule.index', $package->id)->with('success', "Schedule has been successfully synchronized from RAB.");
    }

    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'task_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        $schedule->update($validated);
        return redirect()->back()->with('success', 'Task updated successfully.');
    }

    public function destroy(Schedule $schedule)
    {
        DB::transaction(function () use ($schedule) {
            Schedule::where('parent_id', $schedule->id)->delete();
            $schedule->delete();
        });
        return response()->json(['status' => 'success']);
    }

    public function batchDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:schedules,id']);
        $ids = $request->ids;
        DB::transaction(function () use ($ids) {
            $childIds = Schedule::whereIn('parent_id', $ids)->pluck('id');
            Schedule::destroy($ids);
            Schedule::destroy($childIds);
        });
        return response()->json(['status' => 'success']);
    }

    // GANTI: Logika update urutan yang jauh lebih andal
    public function updateOrder(Request $request)
    {
        $request->validate(['order' => 'required|array', 'order.*.id' => 'required|integer|exists:schedules,id']);
        
        DB::transaction(function () use ($request) {
            foreach ($request->order as $index => $taskData) {
                Schedule::where('id', $taskData['id'])->update([
                    'sort_order' => $index,
                    'parent_id' => $taskData['parent']
                ]);
            }
        });
        
        return response()->json(['status' => 'success']);
    }
}