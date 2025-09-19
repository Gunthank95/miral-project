<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentInternalReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
	{
		Schema::create('document_internal_reviews', function (Blueprint $table) {
			$table->id();
			
			// Menghubungkan ke dokumen utama
			$table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
			
			// Menghubungkan ke pengguna yang melakukan review
			$table->foreignId('user_id')->constrained('users')->onDelete('cascade');
			
			// Status dari review internal ini
			$table->enum('status', ['reviewed', 'revision_needed'])->default('reviewed');

			// Catatan keseluruhan dari pereview
			$table->text('notes')->nullable();
			
			// Menyimpan detail review per gambar dalam format JSON
			$table->json('drawing_reviews')->nullable();

			$table->timestamps();

			// Mencegah satu user mereview dokumen yang sama lebih dari sekali
			$table->unique(['document_id', 'user_id']);
		});
	}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_internal_reviews');
    }
}
