<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlannedProgressTable extends Migration
{
    public function up()
    {
        Schema::create('planned_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->date('week_start_date');
            $table->decimal('weight', 8, 4); // Bobot persentase, misal: 5.1234
            $table->timestamps();

            // Pastikan setiap minggu untuk setiap paket hanya ada satu entri
            $table->unique(['package_id', 'week_start_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('planned_progress');
    }
}