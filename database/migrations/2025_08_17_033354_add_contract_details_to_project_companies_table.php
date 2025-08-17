<?php
// GANTI isi file migrasi
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContractDetailsToProjectCompaniesTable extends Migration
{
    public function up()
    {
        Schema::table('project_companies', function (Blueprint $table) {
            $table->string('contract_number')->nullable()->after('role_in_project');
            $table->decimal('contract_value', 19, 2)->nullable()->after('contract_number');
            $table->date('contract_date')->nullable()->after('contract_value');
            $table->date('start_date_contract')->nullable()->after('contract_date');
            $table->date('end_date_contract')->nullable()->after('start_date_contract');
        });
    }

    public function down()
    {
        Schema::table('project_companies', function (Blueprint $table) {
            $table->dropColumn(['contract_number', 'contract_value', 'contract_date', 'start_date_contract', 'end_date_contract']);
        });
    }
}