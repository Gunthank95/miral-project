<?php
// GANTI isi file migrasi
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetailsToProjectsTable extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('location');
            $table->date('end_date')->nullable()->after('start_date');
            $table->decimal('land_area', 15, 2)->nullable()->after('end_date');
            $table->decimal('building_area', 15, 2)->nullable()->after('land_area');
            $table->integer('floor_count')->nullable()->after('building_area');
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date', 'land_area', 'building_area', 'floor_count']);
        });
    }
}