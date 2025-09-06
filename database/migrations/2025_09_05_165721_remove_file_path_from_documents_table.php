<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveFilePathFromDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
	{
		Schema::table('documents', function (Blueprint $table) {
			// Perintah untuk menghapus kolom 'file_path'
			$table->dropColumn('file_path');
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
