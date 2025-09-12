<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return true; 
    }

    public function view(User $user, Document $document): bool
    {
        return $user->projects()->where('id', $document->package->project_id)->exists();
    }

    public function create(User $user): bool
    {
        $activeProjectId = session('active_project_id'); 
        if (!$activeProjectId) {
            return false;
        }
        return $user->isContractorInProject($activeProjectId);
    }
    
    /**
     * PERBAIKAN FINAL: Aturan review yang lebih ketat.
     */
    public function review(User $user, Document $document): bool
    {
        $projectId = $document->package->project_id;

        $isMKReviewer = $user->isMKInProject($projectId) && $document->status === 'pending';
        $isOwnerReviewer = $user->isOwnerInProject($projectId) && $document->status === 'menunggu_persetujuan_owner';

        return $isMKReviewer || $isOwnerReviewer;
    }

    public function update(User $user, Document $document): bool
    {
        return $user->id === $document->user_id && in_array($document->status, ['pending', 'revision']);
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->id === $document->user_id && in_array($document->status, ['pending', 'revision']);
    }
	
    public function resubmit(User $user, Document $document): bool
    {
        $projectId = $document->package->project_id;
        $isContractor = $user->isContractorInProject($projectId);
        $isSubmitter = ($document->user_id === $user->id);
        $isRevisionStatus = in_array($document->status, ['revision', 'rejected']);

        return $isContractor && $isSubmitter && $isRevisionStatus;
    }
}