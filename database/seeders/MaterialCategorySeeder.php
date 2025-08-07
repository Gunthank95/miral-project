<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MaterialCategory;
use Illuminate\Support\Facades\DB;

class MaterialCategorySeeder extends Seeder
{
    public function run()
    {
        DB::table('material_categories')->truncate();

        // ISI DENGAN NAMA-NAMA KATEGORI ANDA
        $categories = [
        INSERT INTO `task_category` (`id`, `name`, `kode`) VALUES
			(1, 'STRUKTUR', 'STR'),
			(2, 'ARCHITECTURE ', 'ARS'),
			(3, 'MECHANICAL ', 'MEE'),
			(4, 'ELECTRICAL', 'MEM'),
			(5, 'PLUMBING & SANITASI', 'PLB'),
			(6, 'INFORMATIKA TEKNOLOGI ', 'IT'),
			(7, 'AUXILIARY', 'AUX'),
			(8, 'PEKERJAAN TANAH', 'SOIL'),
			(9, 'PEKERJAAN TAMAN ', 'GDN'),
			(10, 'PEKERJAAN PERSIAPAN', 'PRE'),
			(11, 'MECHANICAL & ELECTRICAL ', 'MEEM');
        ];

        foreach ($categories as $categoryName) {
            MaterialCategory::create(['name' => $categoryName]);
        }
    }
}