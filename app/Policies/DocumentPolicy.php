<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentPolicy
{
    use HandlesAuthorization;

    /**
     * Izinkan Super Admin melakukan segalanya.
     */
    public function before(User $user, $ability)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
    }

    /**
     * Siapa yang boleh melihat daftar dokumen? (Semua anggota proyek)
     */
    public function viewAny(User $user): bool
    {
        return true; 
    }

    /**
     * Siapa yang boleh melihat detail sebuah dokumen? (Semua anggota proyek)
     */
    public function view(User $user, Document $document): bool
    {
        // Pastikan user adalah anggota proyek dari dokumen ini
        return $user->projects()->where('id', $document->package->project_id)->exists();
    }

    /**
     * Siapa yang boleh membuat dokumen baru? (Hanya Kontraktor di proyek aktif)
     */
    public function create(User $user): bool
    {
        $activeProjectId = session('active_project_id'); 
        if (!$activeProjectId) {
            return false; // Jika tidak ada proyek aktif, tolak akses
        }
        // Periksa peran user di proyek yang aktif
        return $user->getRoleInProject($activeProjectId) === 'kontraktor';
    }
    
    /**
     * Siapa yang boleh melakukan review?
     * Hanya MK atau Owner, dan hanya jika statusnya 'pending'.
     */
    public function review(User $user, Document $document): bool
    {
        $activeProjectId = session('active_project_id');
        if (!$activeProjectId) {
            return false;
        }

        // 1. Ambil level pengguna dari fungsi helper baru kita
        $userLevel = $user->getLevelInProject($activeProjectId);

        // 2. Ambil level minimum yang dibutuhkan dari config
        $requiredLevel = config('roles.levels.supervisor.level'); // Minimal Supervisor

        // 3. Tolak jika pengguna tidak punya level atau levelnya di bawah standar
        if ($userLevel === null || $userLevel < $requiredLevel) {
            return false;
        }

        // 4. Izinkan jika status dokumennya 'pending'
        return $document->status === 'pending';
    }

    /**
     * Siapa yang boleh mengunggah revisi?
     * Hanya user yang membuat dokumen (Kontraktor), dan hanya jika statusnya 'revision'.
     */
    public function resubmit(User $user, Document $document): bool
    {
        // Hanya pembuat asli dan statusnya harus 'revision'
        return $user->id === $document->user_id && $document->status === 'revision';
    }

    /**
     * Siapa yang boleh mengupdate dokumen?
     * Hanya pembuat asli dan sebelum ada proses approval.
     */
    public function update(User $user, Document $document): bool
    {
        // Boleh update jika dia yang buat DAN statusnya masih 'pending' atau 'revision'.
        return $user->id === $document->user_id && in_array($document->status, ['pending', 'revision']);
    }

    /**
     * Siapa yang boleh menghapus dokumen?
     * Sama seperti update.
     */
    public function delete(User $user, Document $document): bool
    {
        return $user->id === $document->user_id && in_array($document->status, ['pending', 'revision']);
    }
}