<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectCompany extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model ini.
     *
     * @var string
     */
    protected $table = 'project_companies';

    /**
     * Atribut yang bisa diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'company_id',
        'role_in_project',
        // Tambahkan kolom lain dari tabel Anda jika perlu diisi secara massal
    ];

    /**
     * Mendefinisikan relasi ke model Project.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Mendefinisikan relasi ke model Company.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}