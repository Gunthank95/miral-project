<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RegistrationToken; // Tambahkan ini
use Illuminate\Support\Str; // Tambahkan ini

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        return view('superadmin.dashboard');
    }

    /**
     * Menampilkan halaman manajemen token registrasi.
     */
    public function tokensIndex()
    {
        $tokens = RegistrationToken::latest()->paginate(20);
        return view('superadmin.tokens.index', ['tokens' => $tokens]);
    }

    /**
     * Membuat token registrasi baru.
     */
    public function tokensStore(Request $request)
    {
        RegistrationToken::create([
            'token' => Str::random(16),
        ]);

        return back()->with('success', 'Token registrasi baru berhasil dibuat!');
    }
}