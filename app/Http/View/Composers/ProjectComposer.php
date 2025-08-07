<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;

class ProjectComposer
{
    public function compose(View $view)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Ambil daftar semua proyek user untuk dropdown
            $projectIds = $user->projectAssignments->pluck('project_id')->unique();
            $userProjects = Project::whereIn('id', $projectIds)->orderBy('name')->get();

            // Tentukan proyek yang sedang aktif
            $activeProjectId = session('active_project_id');
            $activeProject = null;
            if ($activeProjectId) {
                $activeProject = $userProjects->find($activeProjectId);
            }

            // Kirim kedua data ke view
            $view->with('userProjects', $userProjects)
                 ->with('activeProject', $activeProject);

        } else {
            // Kirim data kosong jika belum login
            $view->with('userProjects', collect())
                 ->with('activeProject', null);
        }
    }
}