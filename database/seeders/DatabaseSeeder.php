<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run()
{
    // 1. Buat atau cari perusahaan internal untuk Super Admin
    $internalCompany = \App\Models\Company::updateOrCreate(
        ['name' => 'Internal System'],
        ['type' => 'internal']
    );

    // 2. Buat atau perbarui user Super Admin dan hubungkan ke perusahaan internal
    \App\Models\User::updateOrCreate(
        ['email' => 'superadmin@example.com'],
        [
            'name' => 'Super Admin',
            'password' => \Illuminate\Support\Facades\Hash::make('password'), // Ganti password jika perlu
            'role' => 'super_admin',
            'company_id' => $internalCompany->id, // Hubungkan ke perusahaan
        ]
    );

    // 3. Panggil seeder lain yang sudah ada
    $this->call([
        // MaterialCategorySeeder::class, // Nonaktifkan sementara jika sudah terisi
        // MaterialSeeder::class,       // Nonaktifkan sementara jika sudah terisi
    ]);
}
}