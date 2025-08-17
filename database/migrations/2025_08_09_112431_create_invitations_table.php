<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
	{
		Schema::create('invitations', function (Blueprint $table) {
			$table->id();
			$table->string('email');
			$table->string('token', 32)->unique();
			$table->foreignId('project_id')->constrained()->onDelete('cascade');
			$table->foreignId('package_id')->nullable()->constrained()->onDelete('cascade');
			$table->foreignId('company_id')->constrained()->onDelete('cascade');
			$table->string('role_in_project'); // Jabatan pengguna di proyek
			$table->timestamp('expires_at');
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
        Schema::dropIfExists('invitations');
    }
}
