<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard utama.
     * Halaman ini sekarang hanya akan menampilkan proyek yang aktif.
     */
    public function index()
    {
        $user = Auth::user();
        $activeProjectId = session('active_project_id');
        $activeProject = null;

        // Cek apakah ada proyek yang aktif di session
        if ($activeProjectId) {
            $activeProject = Project::find($activeProjectId);
        }

        // Jika tidak ada proyek aktif, coba ambil proyek pertama yang bisa diakses user
        if (!$activeProject) {
            $firstAssignment = $user->projectAssignments()->first();
            if ($firstAssignment) {
                $activeProject = Project::find($firstAssignment->project_id);
                // Jika ditemukan, langsung simpan ke session untuk selanjutnya
                if ($activeProject) {
                    session(['active_project_id' => $activeProject->id]);
                }
            }
        }

        // Kirim proyek yang aktif (atau null jika tidak ada) ke view
        return view('dashboard.index', [
            'activeProject' => $activeProject
        ]);
    }
}