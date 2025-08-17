<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDailyReportIdToDailyLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('daily_logs', function (Blueprint $table) {
            // Menambahkan kolom foreign key untuk menghubungkan ke tabel induk daily_reports
            $table->foreignId('daily_report_id')->nullable()->constrained()->onDelete('cascade')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('daily_logs', function (Blueprint $table) {
            //
        });
    }
}
