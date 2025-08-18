<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use App\Models\RegistrationToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminProjectRegisterController extends Controller
{
    /**
     * Menampilkan form registrasi Admin Project.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Memproses pendaftaran Admin Project baru.
     */
    public function register(Request $request)
	{
		$request->validate([
            'project_name' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'company_role' => ['required', 'string', 'in:owner,mk,contractor'],
            'position' => ['required', 'string', 'max:255'],
			'name' => ['required', 'string', 'max:255'],
			'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
			'password' => ['required', 'string', 'min:8', 'confirmed'],
			'token' => ['required', 'string'],
		]);

		$token = \App\Models\RegistrationToken::where('token', $request->token)->whereNull('used_at')->first();

		if (!$token) {
			return back()->withErrors(['token' => 'Token registrasi tidak valid atau sudah digunakan.'])->withInput();
		}

		\Illuminate\Support\Facades\DB::beginTransaction();
		try {
			// TAMBAHKAN: Cek jika perusahaan dengan nama yang sama sudah ada
            $company = \App\Models\Company::firstOrCreate(
                ['name' => $request->company_name],
                ['type' => $request->company_role]
            );

			$user = \App\Models\User::create([
				'name' => $request->name,
				'email' => $request->email,
				'password' => \Illuminate\Support\Facades\Hash::make($request->password),
				'role' => 'admin_project',
                'position' => $request->position,
                'temp_project_name' => $request->project_name,
				'company_id' => $company->id,
			]);

            $user->personnel()->create([
                'company_id' => $user->company_id,
                'name' => $user->name,
                'position' => $user->position,
                'email' => $user->email,
            ]);

			$token->update([
				'email' => $user->email,
				'used_at' => \Carbon\Carbon::now(),
			]);

			\Illuminate\Support\Facades\DB::commit();

			Auth::login($user);

			return redirect()->route('projects.register.create');

		} catch (\Exception $e) {
			\Illuminate\Support\Facades\DB::rollBack();
			return back()->withErrors(['token' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
		}
	}
}