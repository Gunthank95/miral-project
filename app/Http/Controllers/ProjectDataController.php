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
    public function show(Project $project)
    {
        // Memuat semua relasi yang dibutuhkan dalam satu query untuk efisiensi
        $project->load([
            'companies.personnel', // Memuat perusahaan, dan untuk setiap perusahaan, muat juga personilnya
            'packages'
        ]);

        // Mengelompokkan perusahaan berdasarkan peran mereka di proyek
        $companiesByRole = $project->companies->groupBy('pivot.role_in_project');

        // Menghitung nilai proyek dari grand total RAB (jika ada)
        // NOTE: Logika ini memerlukan implementasi RAB. Untuk saat ini, kita beri nilai 0.
        $projectValue = 0; // Ganti dengan logika kalkulasi RAB nanti

        return view('projects.data-proyek', [
            'project' => $project,
            'companiesByRole' => $companiesByRole,
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