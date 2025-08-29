<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\RabItem;
use App\Http\Requests\StoreRabItemRequest;
// HAPUS: 'use Illuminate\Http\Request;' sudah tidak diperlukan lagi di sini
// HAPUS: 'use Illuminate\Support\Facades\DB;' juga tidak digunakan

class RabController extends Controller
{
    /**
     * GANTI: Logika index() disempurnakan untuk menangani kalkulasi hierarki
     */
    public function index(Package $package)
    {
        // Ambil semua item RAB untuk paket ini, diurutkan berdasarkan ID
        $allItems = $package->rabItems()->get()->sortBy('id');

        // Kalkulasi total harga keseluruhan sebagai dasar perhitungan bobot
		$grandTotal = $allItems->whereNotNull('volume')->sum(function ($item) {
			return (float)($item->volume ?? 0) * (float)($item->unit_price ?? 0);
		});

        
        // Bangun struktur hierarki dan hitung totalnya
        $rabTree = $this->buildRabTree($allItems, null, $grandTotal);

        return view('rab.index', [
            'package' => $package,
            'rabItemsTree' => $rabTree,
            'grandTotal' => $grandTotal, // Gunakan grandTotal yang sudah dihitung
        ]);
    }

    public function store(StoreRabItemRequest $request, Package $package)
    {
        $validated = $request->validated();
        $package->rabItems()->create($validated);
        return back()->with('success', 'Item RAB berhasil ditambahkan.');
    }

    public function update(StoreRabItemRequest $request, RabItem $rabItem)
    {
        $validated = $request->validated();
        $rabItem->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'item' => $rabItem]);
        }
        return back()->with('success', 'Item RAB berhasil diperbarui.');
    }

    /**
     * GANTI: Logika buildRabTree() disempurnakan
     * Fungsi ini sekarang menghitung subtotal dan bobot secara rekursif.
     */
    private function buildRabTree($items, $parentId = null, $grandTotal = 0)
    {
        $branch = collect();
        foreach ($items->where('parent_id', $parentId) as $item) {
            // Panggil rekursif untuk anak-anaknya terlebih dahulu
            $item->children = $this->buildRabTree($items, $item->id, $grandTotal);

            // Jika item adalah sub-item (tidak punya volume), subtotalnya adalah jumlah subtotal anak-anaknya
            if (is_null($item->volume)) {
                $item->subtotal = $item->children->sum('subtotal');
            } else {
                // Jika item pekerjaan, hitung total harganya sendiri
                $item->subtotal = ($item->volume ?? 0) * ($item->unit_price ?? 0);
            }
            
            // Hitung bobot berdasarkan subtotal
            if ($grandTotal > 0) {
                $item->weighting = ($item->subtotal / $grandTotal) * 100;
            } else {
                $item->weighting = 0;
            }

            $branch->push($item);
        }
        return $branch;
    }
}