<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentIdToDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
	{
		Schema::table('documents', function (Blueprint $table) {
			// Menambahkan kolom 'parent_id' untuk melacak revisi.
			// Bisa null karena dokumen awal (revisi 0) tidak punya parent.
			$table->unsignedBigInteger('parent_id')->nullable()->after('id');

			// Menambahkan foreign key constraint untuk menjaga integritas data.
			$table->foreign('parent_id')->references('id')->on('documents')->onDelete('cascade');
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
			// Hapus foreign key terlebih dahulu
			$table->dropForeign(['parent_id']);
			// Hapus kolomnya
			$table->dropColumn('parent_id');
		});
	}
}
