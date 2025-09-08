<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Project;
use App\Models\Package;
use App\Models\Company;
use App\Models\User; // Pastikan User di-import
use App\Models\UserProjectRole;
use Illuminate\Support\Facades\Hash; // Import Hash
use Illuminate\Validation\Rules;     // Import Rules
use Illuminate\Validation\Rule;       // Import Rules

class ProjectRegistrationController extends Controller
{
    /**
     * Menampilkan halaman form pendaftaran proyek.
     */
    public function create()
    {
        $user = auth()->user();

        // Ambil data dari user yang sedang login untuk diisi otomatis di form
        $prefilledProjectName = $user->temp_project_name;
        $userCompany = $user->company;

        // Siapkan data untuk dioper ke view
        $viewData = [
            'prefilledProjectName' => $prefilledProjectName,
            'userCompany' => $userCompany,
        ];

        return view('projects.register', $viewData);
    }

    /**
     * Menyimpan data proyek baru.
     */
    public function store(Request $request)
    {
        // Logika store yang lama sudah cukup kompleks dan menangani kasus yang berbeda.
        // Kita akan biarkan untuk saat ini dan fokus pada perbaikan form registrasi admin.
        // Anda bisa merevisi logika ini nanti jika diperlukan.
        $request->validate([
			'project_name' => 'required|string|max:255',
			'owner_company' => 'required|string|max:255',
			'project_location' => 'required|string|max:255',
			'position_title' => 'required|string|max:255',
			'packages' => 'required|array|min:1',
			'packages.*.name' => 'required|string|max:255',
			'packages.*.mk_company' => 'required|string|max:255',
			'packages.*.contractor_company' => 'required|string|max:255',
		]);

		DB::beginTransaction();
		try {
			$user = auth()->user();

            // 1. Update Nama Jabatan pengguna
            $user->update([
                'position_title' => $request->position_title,
            ]);
			
			$ownerCompany = Company::firstOrCreate(
				['name' => $request->owner_company],
				['type' => 'owner']
			);

			$project = Project::create([
				'name' => $request->project_name,
				'owner_company_id' => $ownerCompany->id,
				'location' => $request->project_location,
				'created_by' => auth()->id(),
			]);
			
			$project->users()->attach(auth()->user());
			
			DB::table('project_companies')->insert([
				'project_id' => $project->id,
				'company_id' => $ownerCompany->id,
				'role_in_project' => 'Owner',
				'created_at' => now(),
				'updated_at' => now(),
			]);

			// 2. Ambil level 'Project Manager' dari config
            $roleKey = 'project_manager'; // Default sebagai Project Manager untuk pendaftar pertama
            $roleLevel = config('roles.levels.' . $roleKey . '.level', 60); // 60 adalah fallback
			$roleLevel = config('roles.levels.project_manager.level');	

			// 3. Buat entri di user_project_roles dengan role_level
			UserProjectRole::create([
				'user_id' => Auth::id(),
				'project_id' => $project->id,
				'package_id' => null,
				'role_level' => $roleLevel, // Simpan level jabatan
			]);

			foreach ($request->packages as $packageData) {
				$mkCompany = Company::firstOrCreate(['name' => $packageData['mk_company']], ['type' => 'mk']);
				$contractorCompany = Company::firstOrCreate(['name' => $packageData['contractor_company']], ['type' => 'contractor']);

				$package = $project->packages()->create([
					'name' => $packageData['name']
				]);

				DB::table('project_companies')->insert([
					'project_id' => $project->id,
					'company_id' => $mkCompany->id,
					'role_in_project' => 'MK - ' . $packageData['name'],
					'created_at' => now(),
					'updated_at' => now(),
				]);

				DB::table('project_companies')->insert([
					'project_id' => $project->id,
					'company_id' => $contractorCompany->id,
					'role_in_project' => 'Kontraktor - ' . $packageData['name'],
					'created_at' => now(),
					'updated_at' => now(),
				]);
			}

			// Hapus nama proyek sementara setelah digunakan
			$user->temp_project_name = null;
			$user->save();

			DB::commit();

			session(['active_project_id' => $project->id]);
			return redirect()->route('project.show', $project->id)
                         ->with('success', 'Proyek baru berhasil didaftarkan dan diaktifkan!');

		} catch (\Exception $e) {
			DB::rollBack();
			return back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())->withInput();
		}
    }
}