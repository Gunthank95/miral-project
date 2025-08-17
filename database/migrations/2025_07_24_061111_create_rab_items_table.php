<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRabItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
	{
		Schema::create('rab_items', function (Blueprint $table) {
			$table->id();
			$table->foreignId('package_id')->constrained()->onDelete('cascade');
			$table->unsignedBigInteger('parent_id')->nullable();
			$table->string('item_number')->nullable();
			$table->text('item_name');
			$table->decimal('volume', 15, 4)->nullable();
			$table->string('unit')->nullable();
			$table->decimal('unit_price', 15, 2)->nullable();
			$table->decimal('weighting', 8, 4)->nullable(); // Bobot dalam persen
			$table->timestamps();

			// Menambahkan foreign key untuk parent_id ke tabel itu sendiri
			$table->foreign('parent_id')->references('id')->on('rab_items')->onDelete('cascade');
		});
	}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rab_items');
    }
}
