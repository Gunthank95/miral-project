<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegistrationTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
	{
		Schema::create('registration_tokens', function (Blueprint $table) {
			$table->id();
			$table->string('token', 16)->unique();
			$table->string('email')->nullable(); // Email yang menggunakan token ini
			$table->timestamp('used_at')->nullable(); // Waktu token digunakan
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
        Schema::dropIfExists('registration_tokens');
    }
}
