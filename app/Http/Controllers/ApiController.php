<?php

namespace App\Http\Controllers;

// Models
use App\Models\DailyLog;
use App\Models\RabItem;
use App\Models\DailyReport;
use App\Models\Package;
use App\Models\Document;
use App\Models\DocumentApproval;

// Laravel Facades
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
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
        // Panggil fungsi rekursif baru kita untuk membangun daftar turunan
        $flatList = $this->fetchAndFlattenDescendants($rab_item->id);

        return response()->json($flatList);
    }
	
	/**
     * BARU: Fungsi rekursif untuk mengambil semua turunan dan memformatnya.
     *
     * @param int|null $parentId ID dari item induk untuk memulai.
     * @param int $level Tingkat kedalaman untuk inden.
     * @return array
     */
    private function fetchAndFlattenDescendants($parentId, $level = 0)
	{
		$result = [];

		// Ambil semua anak dari parentId, diurutkan berdasarkan ID (Perbaikan ini tetap dipertahankan)
		$children = RabItem::where('parent_id', $parentId)
			->orderBy('id', 'asc')
			->get();

		foreach ($children as $child) {
			// KEMBALIKAN: Logika untuk membuat prefiks dengan &nbsp; berdasarkan level hirarki
			$prefix = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);

			$result[] = [
				'id' => $child->id,
				// Gabungkan prefiks spasi dengan nama item
				'name' => $prefix . $child->item_number . ' ' . $child->item_name,
				'disabled' => is_null($child->volume),
			];

			// Panggil fungsi ini lagi untuk mencari turunan dari item saat ini
			$descendants = $this->fetchAndFlattenDescendants($child->id, $level + 1);
			
			// Gabungkan hasilnya
			$result = array_merge($result, $descendants);
		}

		return $result;
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
    public function getReviewDetails(Package $package, Document $shop_drawing)
	{
		// Otorisasi: Pastikan pengguna berhak melihat detail review ini
		// Pengguna boleh melihat jika ia boleh me-review ATAU meng-edit review.
		if (Gate::denies('review', $shop_drawing) && Gate::denies('editReview', $shop_drawing)) {
			abort(403, 'Aksi tidak diizinkan.');
		}

		$shop_drawing->load(['drawingDetails', 'rabItems', 'approvals.user.projectRoles' => function ($query) {
			$query->orderBy('created_at', 'asc');
		}]);

		// Eager load data review terakhir jika ada
		$lastReview = DocumentApproval::where('document_id', $shop_drawing->id)
			->where('user_id', auth()->id())
			->latest()
			->first();

		return response()->json([
			'drawings' => $shop_drawing->drawingDetails,
			'rab_items' => $shop_drawing->rabItems,
			'history' => $shop_drawing->approvals,
			'document_status' => $shop_drawing->status,
			'last_review' => $lastReview,
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