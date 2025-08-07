<?php

// database/seeders/DatabaseSeeder.php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Companies
        DB::table('companies')->insert([
            ['id' => 1, 'name' => 'PT. Pemilik Proyek', 'type' => 'owner'],
            ['id' => 2, 'name' => 'PT. Pengawas Proyek', 'type' => 'mk'],
            ['id' => 3, 'name' => 'PT. Kontraktor Struktur', 'type' => 'kontraktor'],
        ]);

        // Seed Users
        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'Admin Owner',
                'email' => 'owner@example.com',
                'password' => Hash::make('password'),
                'company_id' => 1
            ],
            [
                'id' => 2,
                'name' => 'Supervisor MK',
                'email' => 'mk@example.com',
                'password' => Hash::make('password'),
                'company_id' => 2
            ],
            [
                'id' => 3,
                'name' => 'Kontraktor A',
                'email' => 'kontraktor@example.com',
                'password' => Hash::make('password'),
                'company_id' => 3
            ],
        ]);

        // Seed Projects
        DB::table('projects')->insert([
            ['id' => 1, 'name' => 'Proyek Gedung A', 'location' => 'Jakarta', 'created_by' => 1],
        ]);

        // Seed Packages
        DB::table('packages')->insert([
            ['id' => 1, 'project_id' => 1, 'name' => 'Struktur', 'description' => 'Paket pekerjaan struktur']
        ]);

        // Seed User Project Roles
        DB::table('user_project_roles')->insert([
            ['user_id' => 1, 'project_id' => 1, 'package_id' => null, 'role' => 'owner'],
            ['user_id' => 2, 'project_id' => 1, 'package_id' => null, 'role' => 'mk'],
            ['user_id' => 3, 'project_id' => 1, 'package_id' => 1, 'role' => 'kontraktor']
        ]);
		
		$this->call([
			MaterialSeeder::class,
			// Seeder lain bisa ditambahkan di sini nanti
		]);
		
		$this->call([
			MaterialCategorySeeder::class,
			MaterialSeeder::class,
		]);
		
    }
}
