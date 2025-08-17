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
}