<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // GANTI: Hanya menambahkan kolom yang belum ada
        Schema::table('users', function (Blueprint $table) {
            // HAPUS: Baris yang menambahkan 'role' dihapus karena sudah ada.
            
            // Kolom 'position' dan 'temp_project_name' akan ditambahkan setelah 'company_id'
            $table->string('position')->nullable()->after('company_id'); // Untuk Jabatan
            $table->string('temp_project_name')->nullable()->after('position'); // Untuk Nama Proyek sementara
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // GANTI: Hanya menghapus kolom yang kita tambahkan di migrasi ini
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['position', 'temp_project_name']);
        });
    }
}