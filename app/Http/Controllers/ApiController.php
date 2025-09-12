<?php

namespace App\Http\Controllers;

use App\Models\DailyLog;
use App\Models\RabItem;
use App\Models\DailyReport;
use App\Models\Package;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class ApiController extends Controller
{
    /**
     * PERBAIKAN: Fungsi ini sekarang mengirim data lengkap dari RAB.
     */
    public function getRabProgress(RabItem $rab_item)
    {
        // Menjumlahkan semua 'progress_volume' dari daily_logs untuk item RAB ini
        $previousProgressVolume = DailyLog::where('rab_item_id', $rab_item->id)->sum('progress_volume');

        // Mengirim data dalam format JSON
        return response()->json([
            'previous_progress_volume' => $previousProgressVolume,
            'total_contract_volume' => $rab_item->volume,
            'total_contract_weighting' => $rab_item->weighting,
            'unit' => $rab_item->unit,
        ]);
    }

    public function getRabItemChildren(RabItem $rab_item)
    {
        $allItems = RabItem::where('package_id', $rab_item->package_id)->get()->sortBy('id');
        $tree = $this->buildTree($allItems, $rab_item->id);
        $flatList = $this->flattenTreeForDropdown($tree);
        return response()->json($flatList);
    }

    public function checkDuplicateActivity(DailyReport $daily_report, RabItem $rab_item)
    {
        $isDuplicate = $daily_report->activities()
                                    ->where('rab_item_id', $rab_item->id)
                                    ->exists();
        return response()->json([
            'is_duplicate' => $isDuplicate,
        ]);
    }

    public function getLastActivityData(RabItem $rab_item, Package $package)
    {
        $lastLog = DailyLog::where('rab_item_id', $rab_item->id)
                           ->where('package_id', $package->id)
                           ->where(function ($query) {
                               $query->has('materials')->orHas('equipment');
                           })
                           ->latest('log_date')
                           ->first();

        if (!$lastLog) {
            return response()->json(['found' => false]);
        }

        $lastLog->load('materials', 'equipment');

        return response()->json([
            'found' => true,
            'materials' => $lastLog->materials,
            'equipment' => $lastLog->equipment,
        ]);
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
	
	public function getMainRabItems(Request $request, \App\Models\Package $package)
	{
		$searchQuery = $request->input('q');

		$items = \App\Models\RabItem::where('package_id', $package->id)
			->whereNull('parent_id')
			->when($searchQuery, function ($query, $searchQuery) {
				// PERBAIKI: Menggunakan kolom 'item_name'
				return $query->where('item_name', 'like', '%' . $searchQuery . '%');
			})
			// PERBAIKI: Menggunakan kolom 'item_name'
			->orderBy('item_name')
			->limit(50)
			// PERBAIKI: Mengambil 'item_name' dan memberinya alias 'name' agar cocok dengan JavaScript
			->get(['id', 'item_name as name']);

		return response()->json($items);
	}
	
	/**
     * BARU: Mengambil detail lengkap dokumen untuk ditampilkan di modal review.
     */
    public function getReviewDetails(Document $document)
    {
        $this->authorize('review', $document);

        $document->load(['drawingDetails', 'rabItems', 'approvals.user']);

        return response()->json([
            'drawings' => $document->drawingDetails,
            'rab_items' => $document->rabItems,
            'history' => $document->approvals,
            'document_status' => $document->status,
        ]);
    }

    public function getUnreadNotifications()
    {
        return Auth::user()->unreadNotifications;
    }

    public function markAsRead(DatabaseNotification $notification)
    {
        if (Auth::id() === $notification->notifiable_id) {
            $notification->markAsRead();
            return response()->json(['message' => 'Notification marked as read.']);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }
}