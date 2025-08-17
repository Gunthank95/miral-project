<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubmissionDetailsToDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
	{
		Schema::table('documents', function (Blueprint $table) {
			// Kolom untuk mencatat siapa yang harus mereview/menyetujui
			$table->foreignId('submitted_to_user_id')->nullable()->constrained('users')->onDelete('set null')->after('user_id');
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
