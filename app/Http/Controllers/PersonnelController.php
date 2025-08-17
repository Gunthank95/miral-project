<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Personnel;
use App\Models\Project;
use Illuminate\Http\Request;

class PersonnelController extends Controller
{
    /**
     * Menampilkan form untuk menambah personil baru ke sebuah perusahaan dalam proyek.
     */
    public function create(Project $project, Company $company)
    {
        // Policy: Hanya anggota perusahaan terkait yang bisa menambah personil.
        $this->authorize('updateCompanyDetails', [$project, $company]);

        return view('personnel.create', compact('project', 'company'));
    }

    /**
     * Menyimpan personil baru ke database.
     */
    public function store(Request $request, Project $project, Company $company)
    {
        $this->authorize('updateCompanyDetails', [$project, $company]);

        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'nik' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        // 1. Buat data personil dan hubungkan ke perusahaan
        $personnel = $company->personnel()->create($request->all());

        // 2. Lampirkan personil yang baru dibuat ke proyek saat ini
        // Ini belum diimplementasikan, kita tambahkan nanti jika perlu.
        // Untuk saat ini, personil terhubung ke proyek via perusahaan.

        return redirect()->route('projects.data-proyek', $project->id)
                         ->with('status', 'Personil baru berhasil ditambahkan!');
    }

    /**
     * Menampilkan form untuk mengedit data personil.
     */
    public function edit(Project $project, Company $company, Personnel $personnel)
    {
        // Pastikan personil yang diedit adalah milik perusahaan yang benar
        if ($personnel->company_id !== $company->id) {
            abort(404);
        }
        
        $this->authorize('updateCompanyDetails', [$project, $company]);
        
        return view('personnel.edit', compact('project', 'company', 'personnel'));
    }

    /**
     * Memperbarui data personil di database.
     */
    public function update(Request $request, Project $project, Company $company, Personnel $personnel)
    {
        if ($personnel->company_id !== $company->id) {
            abort(404);
        }

        $this->authorize('updateCompanyDetails', [$project, $company]);

        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'nik' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $personnel->update($request->all());

        return redirect()->route('projects.data-proyek', $project->id)
                         ->with('status', 'Data personil berhasil diperbarui!');
    }

    /**
     * Menghapus data personil.
     */
    public function destroy(Project $project, Company $company, Personnel $personnel)
    {
        if ($personnel->company_id !== $company->id) {
            abort(404);
        }

        $this->authorize('updateCompanyDetails', [$project, $company]);

        $personnel->delete();

        return redirect()->route('projects.data-proyek', $project->id)
                         ->with('status', 'Personil berhasil dihapus.');
    }
}