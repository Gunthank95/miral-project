<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('schedules', function (Blueprint $table) {
        $table->id();
        $table->foreignId('package_id')->constrained()->onDelete('cascade');
        $table->string('task_name');
        $table->date('start_date');
        $table->date('end_date');
        $table->integer('progress')->default(0); // Progres dalam persen (0-100)
        $table->string('dependencies')->nullable(); // Untuk menyimpan ID tugas lain yang menjadi prasyarat
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
        Schema::dropIfExists('schedules');
    }
}
