<?php

// Pastikan semua menggunakan backslash (\)
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWaitingOwnerStatusToDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('status', [
                'pending', 
                'approved', 
                'revision', 
                'rejected', 
                'menunggu_persetujuan_owner' // Status Baru
            ])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('status', [
                'pending', 
                'approved', 
                'revision', 
                'rejected'
            ])->default('pending')->change();
        });
    }
}