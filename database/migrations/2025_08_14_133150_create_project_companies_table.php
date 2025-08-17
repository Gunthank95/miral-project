<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
	{
		Schema::create('project_companies', function (Blueprint $table) {
			$table->id();
			$table->foreignId('project_id')->constrained()->onDelete('cascade');
			$table->foreignId('company_id')->constrained()->onDelete('cascade');
			$table->string('role_in_project'); // Misal: Kontraktor Struktur, MK, Owner
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
        Schema::dropIfExists('project_companies');
    }
}
