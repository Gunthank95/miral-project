<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequiresApprovalToDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('documents', function (Blueprint $table) {
        // TAMBAHKAN: Kolom baru untuk menandai apakah dokumen butuh persetujuan.
        // Diletakkan setelah kolom 'category'. Secara default, nilainya false (tidak butuh).
        $table->boolean('requires_approval')->default(false)->after('category');
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
            //
        });
    }
}
