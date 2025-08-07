<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\WorkItem;
use App\Models\WorkItemMaterialNeed;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    public function materialsIndex()
    {
        $materials = Material::orderBy('name')->get();
        return view('admin.materials', [
            'materials' => $materials,
        ]);
    }

    public function materialsStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
        ]);
        Material::create($request->all());
        return back()->with('success', 'Material baru berhasil ditambahkan!');
    }

    /**
     * FUNGSI BARU UNTUK MENANGANI PENYIMPANAN DARI MODAL
     */
    public function materialsStoreModal(Request $request)
	{
		$validated = $request->validate([
			'name' => 'required|string|max:255|unique:materials,name',
			'unit' => 'required|string|max:50',
		]);

		// Buat material baru tanpa menyimpannya dulu
		$material = new Material($validated);
		$material->save(); // Simpan untuk mendapatkan ID (sekarang akan berhasil karena 'code' boleh null)

		// Buat kode unik berdasarkan ID
		$material->code = 'MTR-' . str_pad($material->id, 4, '0', STR_PAD_LEFT);
		$material->save(); // Simpan lagi untuk memasukkan kode

		return response()->json([
			'success' => true,
			'message' => 'Material baru berhasil ditambahkan!',
			'material' => $material,
		]);
	}

    public function workItemsIndex()
    {
        $workItems = WorkItem::orderBy('name')->get();
        return view('admin.work-items', ['workItems' => $workItems]);
    }

    public function workItemsStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
        ]);
        WorkItem::create($request->all());
        return back()->with('success', 'Item pekerjaan baru berhasil ditambahkan!');
    }

    public function workItemNeedsIndex(WorkItem $work_item)
    {
        $materials = Material::orderBy('name')->get();
        $needs = $work_item->materialNeeds()->with('material')->get();
        return view('admin.work-item-needs', [
            'workItem' => $work_item,
            'materials' => $materials,
            'needs' => $needs,
        ]);
    }

    public function workItemNeedsStore(Request $request, WorkItem $work_item)
    {
        $request->validate([
            'material_id' => 'required|exists:materials,id',
            'coefficient' => 'required|numeric',
        ]);

        $existing = $work_item->materialNeeds()->where('material_id', $request->material_id)->exists();
        if ($existing) {
            return back()->with('error', 'Material tersebut sudah ada di dalam daftar.');
        }

        WorkItemMaterialNeed::create([
            'work_item_id' => $work_item->id,
            'material_id' => $request->material_id,
            'coefficient' => $request->coefficient,
        ]);
        return back()->with('success', 'Kebutuhan material berhasil ditambahkan!');
    }

    public function workItemNeedsEdit(WorkItem $work_item, WorkItemMaterialNeed $need)
    {
        return view('admin.work-item-needs-edit', [
            'workItem' => $work_item,
            'need' => $need,
        ]);
    }

    public function workItemNeedsUpdate(Request $request, WorkItem $work_item, WorkItemMaterialNeed $need)
    {
        $request->validate(['coefficient' => 'required|numeric']);
        $need->update(['coefficient' => $request->coefficient]);
        return redirect()->route('admin.work-items.materials.index', $work_item->id)->with('success', 'Koefisien berhasil diperbarui.');
    }

    public function workItemNeedsDestroy(WorkItem $work_item, WorkItemMaterialNeed $need)
    {
        $need->delete();
        return back()->with('success', 'Material berhasil dihapus dari daftar.');
    }
}