<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invitation;
use App\Models\User;
use App\Models\Company;
use App\Models\UserProjectRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvitationAcceptanceController extends Controller
{
    public function showAcceptanceForm($token)
    {
        $invitation = Invitation::where('token', $token)->whereNull('used_at')->where('expires_at', '>', Carbon::now())->firstOrFail();

        return view('auth.invitation-register', ['invitation' => $invitation]);
    }

    public function processAcceptance(Request $request)
    {
        $request->validate([
            'token' => 'required|string|exists:invitations,token',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $invitation = Invitation::where('token', $request->token)->first();

        // Cek ulang untuk keamanan
        if ($invitation->used_at || $invitation->expires_at < Carbon::now()) {
            return redirect()->route('login')->with('error', 'Link undangan tidak valid atau sudah kedaluwarsa.');
        }

        DB::beginTransaction();
        try {
            // Cari user berdasarkan email, atau buat baru jika belum ada
            $user = User::firstOrCreate(
                ['email' => $invitation->email],
                [
                    'name' => $request->name,
                    'password' => Hash::make($request->password),
                    'company_id' => $invitation->company_id,
                    'role' => 'user', // Role default
                ]
            );

            // Tambahkan user ke proyek
            UserProjectRole::create([
                'user_id' => $user->id,
                'project_id' => $invitation->project_id,
                'package_id' => $invitation->package_id,
                'role' => $invitation->role_in_project,
            ]);

            // Tandai undangan sebagai sudah digunakan
            $invitation->update(['used_at' => Carbon::now()]);

            DB::commit();

            Auth::login($user);

            return redirect('/dashboard');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('login')->with('error', 'Terjadi kesalahan saat membuat akun. Silakan coba lagi.');
        }
    }
}