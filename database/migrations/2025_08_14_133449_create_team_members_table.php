<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
	{
		Schema::create('team_members', function (Blueprint $table) {
			$table->id();
			$table->foreignId('project_id')->constrained()->onDelete('cascade');
			$table->foreignId('company_id')->constrained()->onDelete('cascade');
			$table->string('name');
			$table->string('email')->unique();
			$table->string('role'); // Jabatan di proyek
			$table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Akan diisi setelah user mendaftar
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
        Schema::dropIfExists('team_members');
    }
}
