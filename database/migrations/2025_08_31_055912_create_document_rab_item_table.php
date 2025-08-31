<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentRabItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
	{
		Schema::create('document_rab_item', function (Blueprint $table) {
			$table->unsignedBigInteger('document_id');
			$table->unsignedBigInteger('rab_item_id');

			$table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
			$table->foreign('rab_item_id')->references('id')->on('rab_items')->onDelete('cascade');

			$table->primary(['document_id', 'rab_item_id']);
		});
	}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_rab_item');
    }
}
