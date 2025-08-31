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
    if (!Schema::hasColumn('documents', 'title')) {
        $table->string('title')->nullable()->after('name');
    }
    if (!Schema::hasColumn('documents', 'drawing_numbers')) {
        $table->text('drawing_numbers')->nullable()->after('document_number');
    }
    if (!Schema::hasColumn('documents', 'addressed_to')) {
        $table->string('addressed_to')->nullable()->after('drawing_numbers');
    }
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
