<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Menampilkan daftar pengguna yang terikat pada sebuah proyek.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\View\View
     */
    public function index(Project $project)
    {
        // Pastikan pengguna yang login memiliki akses ke proyek ini
        // (Anda bisa menambahkan policy atau gate di sini nanti)

        // Ambil semua pengguna yang terelasi dengan proyek ini
        $users = $project->users()->get();

        // Kirim data ke view
        return view('users.index', [
            'project' => $project,
            'users' => $users,
        ]);
    }
}