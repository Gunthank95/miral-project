<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log; // Untuk debugging jika perlu

class ProjectPolicy
{
    use HandlesAuthorization;

    /**
     * Menentukan apakah user bisa mengedit data umum proyek (seperti tanggal, luas, dll).
     * Hanya Owner dan MK yang bisa.
     */
    public function update(User $user, Project $project): bool
    {
        // Ambil data perusahaan user yang sedang login
        $userCompany = $user->company;

        // Jika perusahaan user adalah 'owner' atau 'mk', izinkan.
        if ($userCompany && in_array($userCompany->type, ['owner', 'mk'])) {
            return true;
        }

        return false;
    }

    /**
     * Menentukan apakah user bisa melihat detail sebuah perusahaan dalam proyek.
     * Aturan:
     * 1. Owner dan MK bisa melihat semua.
     * 2. Kontraktor hanya bisa melihat detail Owner, MK, dan perusahaannya sendiri.
     */
    public function viewCompanyDetails(User $user, Project $project, Company $viewedCompany): bool
    {
        $userCompany = $user->company;
        if (!$userCompany) return false;

        // Aturan #1: Owner dan MK bisa melihat semua
        if (in_array($userCompany->type, ['owner', 'mk'])) {
            return true;
        }

        // Aturan #2: Jika user adalah anggota perusahaan yang sedang dilihat
        if ($userCompany->id === $viewedCompany->id) {
            return true;
        }

        // Aturan #2 (lanjutan): Kontraktor bisa melihat perusahaan Owner dan MK
        if ($userCompany->type === 'contractor' && in_array($viewedCompany->type, ['owner', 'mk'])) {
            return true;
        }

        return false;
    }

    /**
     * Menentukan apakah user bisa mengedit detail sebuah perusahaan (kontrak, personil) dalam proyek.
     * Aturan: Hanya anggota dari perusahaan itu sendiri yang bisa mengedit.
     */
    public function updateCompanyDetails(User $user, Project $project, Company $companyToUpdate): bool
    {
        // Hanya izinkan jika ID perusahaan user sama dengan ID perusahaan yang akan diupdate.
        return $user->company_id === $companyToUpdate->id;
    }

}