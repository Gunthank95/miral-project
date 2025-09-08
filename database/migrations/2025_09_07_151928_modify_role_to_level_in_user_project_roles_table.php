<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyRoleToLevelInUserProjectRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_project_roles', function (Blueprint $table) {
            // 1. Tambahkan kolom baru untuk menyimpan level sebagai angka (integer).
            //    Kita letakkan setelah project_id. `default(0)` untuk keamanan data lama.
            $table->unsignedTinyInteger('role_level')->default(0)->after('project_id');

            // 2. Hapus kolom 'role' yang lama karena sudah tidak digunakan.
            $table->dropColumn('role');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_project_roles', function (Blueprint $table) {
            // Jika di-rollback, kembalikan seperti semula
            // 1. Tambahkan kembali kolom 'role' yang lama.
            $table->string('role')->after('project_id');

            // 2. Hapus kolom 'role_level' yang baru.
            $table->dropColumn('role_level');
        });
    }
}