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
     * Siapa yang boleh membuat dokumen baru?
     * Logika baru: Pengguna yang perusahaannya adalah Kontraktor di proyek ini.
     */
    public function create(User $user): bool
    {
        $activeProjectId = session('active_project_id'); 
        if (!$activeProjectId) {
            return false;
        }
        
        // Gunakan fungsi helper baru kita
        return $user->isContractorInProject($activeProjectId);
    }
    
    /**
     * Siapa yang boleh melakukan review?
     * Hanya MK atau Owner, dan hanya jika statusnya 'pending'.
     */
    public function review(User $user, Document $document): bool
    {
        $projectId = $document->package->project_id;
        $userLevel = $user->getLevelInProject($projectId);
        $requiredLevel = config('roles.levels.supervisor.level');

        // Aturan untuk MK:
        // Levelnya harus >= Supervisor DAN status dokumen 'pending'
        $isMKReviewer = ($userLevel !== null && $userLevel >= $requiredLevel && $document->status === 'pending');

        // Aturan untuk Owner:
        // Dia adalah Owner di proyek ini DAN status dokumen 'menunggu_persetujuan_owner'
        $isOwnerReviewer = ($user->isOwnerInProject($projectId) && $document->status === 'menunggu_persetujuan_owner');

        // Izinkan jika salah satu dari dua kondisi di atas terpenuhi.
        return $isMKReviewer || $isOwnerReviewer;
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
	
	// ... (fungsi review sebelumnya) ...

    /**
     * Siapa yang boleh mengajukan revisi (resubmit) dokumen?
     * Logika: Hanya Kontraktor yang mengajukan dan dokumen berstatus 'revision'.
     */
    public function resubmit(User $user, Document $document): bool
    {
        $projectId = $document->package->project_id;

        // 1. Pastikan pengguna adalah Kontraktor di proyek ini
        $isContractor = $user->isContractorInProject($projectId);

        // 2. Pastikan dokumen ini diajukan oleh user yang sama
        $isSubmitter = ($document->user_id === $user->id);

        // 3. Pastikan status dokumen adalah 'revision'
        $isRevisionStatus = ($document->status === 'revision');

        return $isContractor && $isSubmitter && $isRevisionStatus;
    }
}