<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Menampilkan form edit profil.
     */
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Memperbarui informasi profil pengguna.
     */
    // GANTI: method update() di ProfileController.php
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users')->ignore($user->id)],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'certifications' => ['nullable', 'string'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $user->profile_photo_path = $path;
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone_number = $request->phone_number;
        $user->certifications = $request->certifications;
        
        $user->save();

        // GANTI: Logika update atau buat data Personnel
        $user->personnel()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'company_id' => $user->company_id,
                'name' => $user->name,
                // TAMBAHKAN: Memberikan nilai default jika position kosong
                'position' => $user->position ?? 'Jabatan Belum Diisi',
                'email' => $user->email,
                'phone_number' => $user->phone_number,
            ]
        );

        return redirect()->route('profile.edit')->with('status', 'Profil berhasil diperbarui!');
    }
}