<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHierarchyToSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Kolom untuk menyimpan ID tugas induknya
            $table->unsignedBigInteger('parent_id')->nullable()->after('id');
            // Kolom untuk menyimpan ID asli dari RAB untuk mencegah duplikasi impor
            $table->unsignedBigInteger('rab_item_id')->nullable()->unique()->after('package_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Kolom yang akan dihapus jika migrasi di-rollback
            $table->dropColumn(['parent_id', 'rab_item_id']);
        });
    }
}