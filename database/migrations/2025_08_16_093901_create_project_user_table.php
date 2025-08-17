<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_user', function (Blueprint $table) {
            // Hapus $table->id(); karena kita tidak butuh ID unik di sini.
            // Kita akan gunakan kombinasi project_id dan user_id sebagai kunci utama.

            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Set primary key agar tidak ada duplikat user di proyek yang sama
            $table->primary(['project_id', 'user_id']);

            // Timestamps tidak wajib untuk pivot table, tapi bisa berguna
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_user');
    }
}
