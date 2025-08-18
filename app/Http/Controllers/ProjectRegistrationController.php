<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Project;
use App\Models\Package;
use App\Models\Company;
use App\Models\UserProjectRole;

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
        $userCompany = $user->company; // Asumsi relasi 'company' sudah ada di model User

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
		$request->validate([
			'project_name' => 'required|string|max:255',
			'owner_company' => 'required|string|max:255',
			'project_location' => 'required|string|max:255',
			'packages' => 'required|array|min:1',
			'packages.*.name' => 'required|string|max:255',
			'packages.*.mk_company' => 'required|string|max:255',
			'packages.*.contractor_company' => 'required|string|max:255',
		]);

		DB::beginTransaction();
		try {
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

			UserProjectRole::create([
				'user_id' => Auth::id(),
				'project_id' => $project->id,
				'package_id' => null,
				'role' => 'Admin Project',
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
			$user = auth()->user();
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