<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentDocumentIdToDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('parent_document_id')
                  ->nullable() // Bisa null jika ini bukan dokumen revisi
                  ->after('status')
                  ->constrained('documents') // Foreign key ke tabel documents itu sendiri
                  ->onDelete('set null'); // Jika dokumen induk dihapus, set ini jadi null
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
            $table->dropForeign(['parent_document_id']);
            $table->dropColumn('parent_document_id');
        });
    }
}