<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;

    protected $table = 'invitations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'token',
        'project_id',
        'package_id',
        'company_id',
        'role_in_project',
        'expires_at',
        'used_at', // Pastikan ini juga ada di fillable
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Mendapatkan data perusahaan yang terhubung dengan undangan ini.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}