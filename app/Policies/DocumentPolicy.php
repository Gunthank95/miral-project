<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create models.
     * Aturan: Hanya user dengan peran 'kontraktor' yang bisa membuat dokumen baru.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
	{
		// Gunakan 'active_project_id', ini adalah kunci session yang benar
		$activeProjectId = session('active_project_id'); 

		if (!$activeProjectId) {
			return false; // Jika tidak ada proyek aktif, tolak akses
		}

		// Periksa peran user di proyek yang aktif
		return $user->getRoleInProject($activeProjectId) === 'kontraktor';
	}

	public function review(User $user, Document $document)
	{
		// Gunakan 'active_project_id', ini adalah kunci session yang benar
		$activeProjectId = session('active_project_id');

		if (!$activeProjectId) {
			return false;
		}

		$userRole = $user->getRoleInProject($activeProjectId);

		// Izinkan jika perannya 'mk' atau 'owner'
		return in_array($userRole, ['mk', 'owner']);
	}
}