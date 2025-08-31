<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReviewDetailsToDocumentApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('document_approvals', function (Blueprint $table) {
        // TAMBAHKAN: Kolom untuk membuat riwayat berjenjang (induk-anak)
        $table->unsignedBigInteger('parent_id')->nullable()->after('id');
        
        // TAMBAHKAN: Kolom untuk menyimpan catatan dari MK
        $table->text('notes')->nullable()->after('status');

        // TAMBAHKAN: Kolom untuk menyimpan path file yang di-review oleh MK
        $table->string('reviewed_file_path')->nullable()->after('notes');

        // TAMBAHKAN: Relasi foreign key untuk parent_id
        $table->foreign('parent_id')->references('id')->on('document_approvals')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('document_approvals', function (Blueprint $table) {
            //
        });
    }
}
