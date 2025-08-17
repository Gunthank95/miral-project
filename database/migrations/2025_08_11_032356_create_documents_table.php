<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
	{
		Schema::create('documents', function (Blueprint $table) {
			$table->id();
			$table->foreignId('package_id')->constrained()->onDelete('cascade');
			$table->foreignId('user_id')->constrained()->onDelete('cascade'); // User yang mengupload
			$table->string('name'); // Nama dokumen, misal: Shop Drawing Pondasi
			$table->string('category'); // Misal: Shop Drawing, As-Built Drawing, Metode Kerja
			$table->string('file_path');
			$table->integer('revision')->default(0);
			$table->string('status')->default('pending'); // pending, approved, rejected
			$table->timestamps();
		});
	}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documents');
    }
}
