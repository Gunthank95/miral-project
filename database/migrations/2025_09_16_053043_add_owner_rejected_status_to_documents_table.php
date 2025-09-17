<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddOwnerRejectedStatusToDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Menambahkan 'owner_rejected' ke dalam daftar ENUM yang ada
        DB::statement("ALTER TABLE documents MODIFY COLUMN status ENUM('pending', 'revision', 'approved', 'rejected', 'menunggu_persetujuan_owner', 'owner_rejected') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Mengembalikan daftar ENUM ke kondisi semula jika migration di-rollback
        DB::statement("ALTER TABLE documents MODIFY COLUMN status ENUM('pending', 'revision', 'approved', 'rejected', 'menunggu_persetujuan_owner') NOT NULL DEFAULT 'pending'");
    }
}