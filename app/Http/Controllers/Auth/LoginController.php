<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Tampilkan halaman login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Proses login
    public function login(Request $request)
	{
		$credentials = $request->validate([
			'email' => ['required', 'email'],
			'password' => ['required'],
		]);

		if (Auth::attempt($credentials)) {
			$request->session()->regenerate();

			$user = Auth::user();

			// PERBAIKAN: Periksa peran pengguna setelah login
			if ($user->role === 'super_admin') {
				return redirect()->route('superadmin.dashboard');
			}

			return redirect()->intended('/dashboard');
		}

		return back()->withErrors([
			'email' => 'Email atau password salah.',
		])->onlyInput('email');
	}

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Mengarahkan kembali ke halaman login setelah logout
        return redirect('/login');
    }
}