<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use App\Models\Project;
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

    public function create(User $user, Project $project): bool
	{
		// Langsung periksa apakah pengguna adalah kontraktor di proyek yang diberikan.
		return $user->isContractorInProject($project->id);
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
	
	public function editReview(User $user, Document $document): bool
	{
		$projectId = $document->package->project_id;

		// MK bisa edit review-nya jika statusnya sudah dikirim ke owner
		$canMKEdit = $user->isMKInProject($projectId) && $document->status === 'menunggu_persetujuan_owner';

		// Owner bisa edit keputusannya jika statusnya sudah final
		$canOwnerEdit = $user->isOwnerInProject($projectId) && in_array($document->status, ['approved', 'owner_rejected']);

		return $canMKEdit || $canOwnerEdit;
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
		
		// PERBAIKAN DI SINI: Tambahkan 'owner_rejected'
		$isRevisionStatus = in_array($document->status, ['revision', 'owner_rejected']);

		return $isContractor && $isSubmitter && $isRevisionStatus;
	}
}