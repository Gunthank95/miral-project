<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectCompanyController extends Controller
{
    /**
     * Menampilkan form untuk menambahkan perusahaan baru ke proyek.
     */
    public function create(Project $project)
    {
        // Policy: Hanya Owner dan MK yang bisa mengakses halaman ini.
        $this->authorize('update', $project);

        $existingCompanyIds = $project->companies()->pluck('companies.id');
        $companies = Company::whereNotIn('id', $existingCompanyIds)->get();

        return view('projects.companies.create', compact('project', 'companies'));
    }

    /**
     * Menyimpan perusahaan baru yang ditambahkan ke proyek.
     */
    public function store(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'role_in_project' => 'required|string|max:255',
            'contract_number' => 'nullable|string|max:255',
            'contract_value' => 'nullable|numeric',
            'contract_date' => 'nullable|date',
        ]);

        // 'attach' akan menambahkan relasi baru di tabel project_companies
        $project->companies()->attach($request->company_id, [
            'role_in_project' => $request->role_in_project,
            'contract_number' => $request->contract_number,
            'contract_value' => $request->contract_value,
            'contract_date' => $request->contract_date,
        ]);

        return redirect()->route('projects.data-proyek', $project->id)
                         ->with('status', 'Perusahaan berhasil ditambahkan ke proyek!');
    }

    /**
     * Menampilkan form untuk mengedit detail perusahaan dalam proyek.
     */
    public function edit(Project $project, Company $company)
    {
        // Policy: Hanya anggota perusahaan terkait yang bisa mengedit.
        $this->authorize('updateCompanyDetails', [$project, $company]);
        
        // Ambil data pivot (data kontrak) dari relasi
        $pivotData = $project->companies()->where('company_id', $company->id)->first()->pivot;

        return view('projects.companies.edit', compact('project', 'company', 'pivotData'));
    }

    /**
     * Memperbarui detail perusahaan dalam proyek.
     */
    public function update(Request $request, Project $project, Company $company)
    {
        $this->authorize('updateCompanyDetails', [$project, $company]);

        $request->validate([
            'role_in_project' => 'required|string|max:255',
            'contract_number' => 'nullable|string|max:255',
            'contract_value' => 'nullable|numeric',
            'contract_date' => 'nullable|date',
        ]);

        // 'updateExistingPivot' akan memperbarui data di tabel project_companies
        $project->companies()->updateExistingPivot($company->id, [
            'role_in_project' => $request->role_in_project,
            'contract_number' => $request->contract_number,
            'contract_value' => $request->contract_value,
            'contract_date' => $request->contract_date,
        ]);

        return redirect()->route('projects.data-proyek', $project->id)
                         ->with('status', 'Data perusahaan berhasil diperbarui!');
    }
}