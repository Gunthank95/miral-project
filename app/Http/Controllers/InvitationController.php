<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Invitation;
use App\Models\UserProjectRole;
use App\Mail\SendInvitationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InvitationController extends Controller
{
    /**
     * Menampilkan halaman manajemen tim dan undangan.
     */
    public function index(Project $project)
	{
		// Menggunakan relasi 'companies()' yang baru untuk mengambil data yang benar
		$companies = $project->companies()->orderBy('name')->get();

		// Ambil anggota tim yang sudah aktif
		$teamMembers = $project->userAssignments()->with('user.company')->get();

		// Ambil undangan yang masih terkirim
		$pendingInvitations = $project->invitations()->with('company')->whereNull('used_at')->get();

		$roles = config('app.project_roles', []); // Ambil kamus peran

		return view('invitations.index', [
			'project' => $project,
			'companies' => $companies,
			'teamMembers' => $teamMembers,
			'pendingInvitations' => $pendingInvitations,
			'roles' => $roles, // Kirim kamus peran ke view
		]);
	}	

    /**
     * Menyimpan dan mengirim undangan baru.
     */
    public function store(Request $request, Project $project)
	{
		$request->validate([
			'email' => 'required|email',
			'company_id' => 'required|exists:companies,id',
			'package_id' => 'nullable|exists:packages,id',
			'role_in_project' => 'required|string|max:255',
			'role' => 'required|string',
		]);

		$existingInvitation = $project->invitations()->where('email', $request->email)->whereNull('used_at')->first();
		if ($existingInvitation) {
			return back()->with('error', 'Email ini sudah memiliki undangan aktif untuk proyek ini.');
		}

		$token = \Illuminate\Support\Str::random(32);

		$invitation = \App\Models\Invitation::create([
			'email' => $request->email,
			'token' => $token,
			'project_id' => $project->id,
			'package_id' => $request->package_id,
			'company_id' => $request->company_id,
			'role_in_project' => $request->role_in_project,
			'role' => $request->role,
			'expires_at' => \Carbon\Carbon::now()->addDays(7),
		]);

		// Kirim email ke pengguna yang diundang
		Mail::to($request->email)->send(new SendInvitationMail($invitation));

		return back()->with('success', 'Undangan berhasil dikirim ke ' . $request->email);
	}
}