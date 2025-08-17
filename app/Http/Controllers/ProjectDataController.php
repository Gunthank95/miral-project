<?php
// GANTI isi file ProjectDataController.php
namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectDataController extends Controller
{
    /**
     * Menampilkan halaman data utama proyek.
     */
    // GANTI: method show() di ProjectDataController.php
    public function show(Project $project)
    {
        $project->load(['companies.personnel', 'packages']);

        // Mengelompokkan perusahaan berdasarkan TIPE, bukan peran spesifik
        $companiesByType = $project->companies->groupBy('type');
        
        // Menyiapkan data owner secara terpisah
        $owner = $project->companies()->where('type', 'owner')->first();

        $projectValue = 0; // Ganti dengan logika kalkulasi RAB nanti

        return view('projects.data-proyek', [
            'project' => $project,
            'owner' => $owner,
            'companiesByType' => $companiesByType,
            'projectValue' => $projectValue,
        ]);
    }
	
	/**
     * Menampilkan form untuk mengedit data utama proyek.
     */
    public function edit(Project $project)
    {
        // 'Satpam' bekerja di sini: Memeriksa apakah user boleh 'update' proyek ini.
        $this->authorize('update', $project);

        return view('projects.edit-data', [
            'project' => $project,
        ]);
    }

    /**
     * Memperbarui data utama proyek di database.
     */
    public function update(Request $request, Project $project)
    {
        // 'Satpam' bekerja lagi: Pastikan hanya yang berhak yang bisa menyimpan.
        $this->authorize('update', $project);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'land_area' => 'nullable|numeric|min:0',
            'building_area' => 'nullable|numeric|min:0',
            'floor_count' => 'nullable|integer|min:0',
        ]);

        $project->update($validatedData);

        return redirect()->route('projects.data-proyek', $project->id)
                         ->with('status', 'Data umum proyek berhasil diperbarui!');
    }
}