<?php
// GANTI: Isi file migrasi
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToPersonnelTable extends Migration
{
    public function up()
    {
        Schema::table('personnel', function (Blueprint $table) {
            // Kolom user_id bisa null karena mungkin ada personil yang tidak punya akun login
            // Unik untuk memastikan satu user hanya punya satu data personil
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->onDelete('set null')->after('id');
        });
    }

    public function down()
    {
        Schema::table('personnel', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
}