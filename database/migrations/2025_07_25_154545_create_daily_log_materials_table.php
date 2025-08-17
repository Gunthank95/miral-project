<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyLogMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_log_materials', function (Blueprint $table) {
        	$table->id();
			$table->foreignId('daily_log_id')->constrained()->onDelete('cascade');
			$table->foreignId('material_id')->constrained()->onDelete('cascade');
			$table->decimal('quantity', 15, 4);
			$table->string('unit');
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
        Schema::dropIfExists('daily_log_materials');
    }
}
