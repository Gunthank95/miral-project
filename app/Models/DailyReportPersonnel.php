<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReportPersonnel extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model ini.
     *
     * @var string
     */
    protected $table = 'daily_report_personnel';

    protected $fillable = [
        'daily_report_id',
        'role',
        'company_type',
        'count',
    ];
}