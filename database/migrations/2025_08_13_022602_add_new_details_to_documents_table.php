<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewDetailsToDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
	{
		Schema::table('documents', function (Blueprint $table) {
			$table->string('document_number')->nullable()->after('name');
			$table->text('notes')->nullable()->after('status');
			// Mengubah kolom submitted_to_user_id agar tidak constrained ke users
			// karena bisa jadi ditujukan ke grup/peran
			$table->string('submitted_to')->nullable()->after('user_id'); 
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
