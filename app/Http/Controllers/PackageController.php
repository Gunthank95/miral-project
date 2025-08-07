<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Project;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project, Package $package)
    {
        // Pastikan paket yang diminta benar-benar milik proyek yang diberikan
        if ($package->project_id != $project->id) {
            abort(404);
        }

        // Di sini Anda akan menampilkan halaman detail paket.
        // Anda bisa memuat relasi seperti daily logs, RAB, dll.
        $package->load('dailyLogs.photos', 'rabItems');

        return view('packages.show', [
            'project' => $project,
            'package' => $package
        ]);
    }
}