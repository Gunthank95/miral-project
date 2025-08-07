<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Menampilkan halaman detail sebuah proyek.
     */
    public function show(Project $project)
    {
        // 1. Dapatkan user yang sedang login
        $user = Auth::user();

        // 2. Cek hak akses user untuk proyek ini dari tabel penghubung
        $assignment = $user->projectAssignments()
                           ->where('project_id', $project->id)
                           ->first();

        // 3. Jika user tidak terdaftar di proyek ini, tampilkan error 403 (Akses Ditolak)
        if (!$assignment) {
            abort(403, 'ANDA TIDAK MEMILIKI AKSES KE PROYEK INI.');
        }

        // 4. Siapkan variabel untuk menampung paket pekerjaan
        $packages = null;

        // 5. Logika Filter: Tampilkan paket berdasarkan hak akses
        if (is_null($assignment->package_id)) {
            // Jika package_id KOSONG (untuk Owner/MK), tampilkan SEMUA paket di proyek ini
            $packages = $project->packages;
        } else {
            // Jika package_id TERISI (untuk Kontraktor), tampilkan HANYA paket yang ditugaskan
            // Kita kumpulkan semua ID paket jika kontraktor punya banyak tugas di 1 proyek
            $packageIds = $user->projectAssignments()
                                ->where('project_id', $project->id)
                                ->pluck('package_id');
            
            $packages = $project->packages()->whereIn('id', $packageIds)->get();
        }

        // 6. Kirim data yang sudah difilter ke view
        return view('projects.show', [
            'project' => $project,
            'packages' => $packages,
        ]);
    }
}