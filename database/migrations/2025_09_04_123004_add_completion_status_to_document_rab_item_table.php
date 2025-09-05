<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompletionStatusToDocumentRabItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
	{
		Schema::table('document_rab_item', function (Blueprint $table) {
			// Menambahkan kolom untuk status kelengkapan gambar terhadap item pekerjaan
			$table->string('completion_status')->default('belum')->after('rab_item_id'); // Opsi: 'belum', 'lengkap'
		});
	}

	public function down()
	{
		Schema::table('document_rab_item', function (Blueprint $table) {
			$table->dropColumn('completion_status');
		});
	}
}
