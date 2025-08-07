<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\RabItem;
use Illuminate\Http\Request;

class RabController extends Controller
{
    /**
     * Menampilkan halaman RAB.
     */
    public function index(Package $package)
    {
        // Ambil semua item RAB untuk paket ini, diurutkan berdasarkan ID
        $allItems = $package->rabItems()->get()->sortBy('id');

        // Bangun struktur hierarki dan hitung totalnya
        $rabTree = $this->buildRabTree($allItems);
        $grandTotal = collect($rabTree)->sum('subtotal');

        return view('rab.index', [
            'package' => $package,
            'rabItemsTree' => $rabTree,
            'grandTotal' => $grandTotal,
        ]);
    }

    /**
     * Mengimpor data RAB dari file CSV dengan logika "updateOrCreate".
     */
    public function import(Request $request, Package $package)
    {
        $request->validate(['rab_file' => 'required|mimes:csv,txt']);

        $file = $request->file('rab_file');
        
        $lines = file($file->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (count($lines) < 2) {
            return back()->with('error', 'File CSV kosong atau hanya berisi header.');
        }
        
        $delimiter = ';';
        array_shift($lines);
        
        $dataRows = [];
        foreach ($lines as $line) {
            $dataRows[] = str_getcsv($line, $delimiter);
        }

        // --- TAHAP 1: Hitung Grand Total dari file CSV ---
        $grandTotal = 0;
        foreach ($dataRows as $row) {
            $volume = isset($row[2]) && $row[2] !== '' ? (float)str_replace(',', '.', $row[2]) : 0;
            $unitPrice = isset($row[4]) && $row[4] !== '' ? (float)str_replace(',', '.', $row[4]) : 0;
            $grandTotal += $volume * $unitPrice;
        }

        if ($grandTotal == 0) {
            return back()->with('error', 'Gagal menghitung Grand Total. Pastikan format volume dan harga benar.');
        }

        // --- TAHAP 2: Lakukan Update atau Create untuk setiap item ---
        $itemNumbersInCsv = [];
        foreach ($dataRows as $row) {
            $itemNumber = $row[0] ?? null;
            $itemName = $row[1] ?? null;
            if (!$itemNumber || !$itemName) continue;

            $itemNumbersInCsv[] = $itemNumber;

            $volume = isset($row[2]) && $row[2] !== '' ? (float)str_replace(',', '.', $row[2]) : null;
            $unit = $row[3] ?? null;
            $unitPrice = isset($row[4]) && $row[4] !== '' ? (float)str_replace(',', '.', $row[4]) : null;

            $totalPrice = ($volume ?? 0) * ($unitPrice ?? 0);
            $weighting = ($totalPrice / $grandTotal) * 100;

            $parentId = null;
            $parts = explode('.', $itemNumber);
            if (count($parts) > 1) {
                array_pop($parts);
                $parentNumber = implode('.', $parts);
                $parentItem = $package->rabItems()->where('item_number', $parentNumber)->first();
                if ($parentItem) {
                    $parentId = $parentItem->id;
                }
            }

            RabItem::updateOrCreate(
                [
                    'package_id' => $package->id,
                    'item_number' => $itemNumber,
                ],
                [
                    'parent_id'   => $parentId,
                    'item_name'   => $itemName,
                    'volume'      => $volume,
                    'unit'        => $unit,
                    'unit_price'  => $unitPrice,
                    'weighting'   => $weighting,
                ]
            );
        }

        // Hapus item dari database yang tidak ada lagi di file CSV baru
        $package->rabItems()->whereNotIn('item_number', $itemNumbersInCsv)->delete();

        return back()->with('success', 'Data RAB berhasil disinkronkan!');
    }
    
    /**
     * PERBAIKAN UTAMA: Fungsi ini sekarang juga menjumlahkan bobot.
     */
    private function buildRabTree($items, $parentId = null)
    {
        $branch = collect();
        foreach ($items->where('parent_id', $parentId) as $child) {
            $itemTotal = ($child->volume ?? 0) * ($child->unit_price ?? 0);
            
            // Panggil fungsi ini untuk semua anak-anaknya terlebih dahulu
            $child->children = $this->buildRabTree($items, $child->id);
            
            // Hitung subtotal harga
            $child->subtotal = $itemTotal + $child->children->sum('subtotal');
            
            // Hitung subtotal bobot. Bobot item induk adalah bobotnya sendiri (yang 0)
            // ditambah total bobot dari semua anak-anaknya.
            $child->weighting = ($child->weighting ?? 0) + $child->children->sum('weighting');

            $branch->push($child);
        }
        return $branch;
    }
}