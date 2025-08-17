<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToProjectsAndPackages extends Migration
{
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

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });
    }

    /**
     * Cek apakah foreign key sudah ada agar tidak error duplicate
     */
    protected function foreignKeyExists(string $table, string $column): bool
    {
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $foreignKeys = $sm->listTableForeignKeys($table);
        foreach ($foreignKeys as $foreignKey) {
            if (in_array($column, $foreignKey->getLocalColumns())) {
                return true;
            }
        }
        return false;
    }
}
