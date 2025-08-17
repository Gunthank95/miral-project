<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Document; // Pastikan ini ditambahkan
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    public function index(Request $request, Package $package)
    {
        $categories = [
            'for_con' => 'For-Con Drawing',
            'metode_kerja' => 'Metode Kerja',
            'shop_drawing' => 'Shop Drawing',
            'as_built' => 'As-Built Drawing',
        ];

        $activeCategoryKey = $request->input('category', array_key_first($categories));

        $documents = $package->documents()
                             ->where('category', $categories[$activeCategoryKey])
                             ->with('user')
                             ->latest()
                             ->get();

        return view('documents.index', [
            'package' => $package,
            'documents' => $documents,
            'categories' => $categories,
            'activeCategory' => $activeCategoryKey,
        ]);
    }

    /**
     * Menampilkan form untuk mengunggah dokumen baru.
     */
    public function create(Request $request, Package $package)
    {
        $categoryKey = $request->input('category');
        $categories = [
            'for_con' => 'For-Con Drawing',
            'metode_kerja' => 'Metode Kerja',
            'shop_drawing' => 'Shop Drawing',
            'as_built' => 'As-Built Drawing',
        ];

        // Pastikan kategori yang dikirim valid
        if (!array_key_exists($categoryKey, $categories)) {
            abort(404);
        }

        return view('documents.create', [
            'package' => $package,
            'categoryKey' => $categoryKey,
            'categoryName' => $categories[$categoryKey],
        ]);
    }

    /**
     * Menyimpan dokumen baru.
     */
    public function store(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png,dwg,xls,xlsx,doc,docx|max:20480', // Maks 20MB
        ]);

        $filePath = $request->file('document_file')->store('documents/' . $package->id, 'public');

        $package->documents()->create([
            'user_id' => Auth::id(),
            'category' => $validated['category'],
            'name' => $validated['name'],
            'file_path' => $filePath,
            'status' => 'pending', // Status default
        ]);

        $categoryKey = array_search($validated['category'], [
            'for_con' => 'For-Con Drawing',
            'metode_kerja' => 'Metode Kerja',
            'shop_drawing' => 'Shop Drawing',
            'as_built' => 'As-Built Drawing',
        ]);

        return redirect()->route('documents.index', ['package' => $package->id, 'category' => $categoryKey])
                         ->with('success', 'Dokumen berhasil diunggah.');
    }
}