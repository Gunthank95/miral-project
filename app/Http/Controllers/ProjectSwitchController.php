<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectSwitchController extends Controller
{
    public function switchProject(Request $request)
    {
        // 1. Validasi input, pastikan project_id ada dan berupa angka
        $request->validate([
            'project_id' => 'required|integer',
        ]);

        $projectId = $request->input('project_id');

        // 2. Cek apakah user punya akses ke proyek yang dipilih
        $user = Auth::user();
        $hasAccess = $user->projectAssignments()->where('project_id', $projectId)->exists();

        if ($hasAccess) {
            // 3. Jika punya akses, simpan ID proyek ke dalam session
            session(['active_project_id' => $projectId]);
        } else {
            // Jika tidak punya akses, abaikan saja atau beri pesan error
            return back()->with('error', 'Akses ke proyek ditolak.');
        }

        // 4. Arahkan pengguna kembali ke halaman sebelumnya
        return back();
    }
}