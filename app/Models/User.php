<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Project;
use Illuminate\Support\Facades\Storage; // TAMBAHKAN ini

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // GANTI: Tambahkan semua field profil baru
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'role',
        'position',
        'temp_project_name',
        'phone_number',
        'profile_photo_path',
        'certifications',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // TAMBAHKAN: Accessor untuk URL foto profil
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo_path) {
            return Storage::url($this->profile_photo_path);
        }

        // URL ke gambar default jika tidak ada foto profil
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=random';
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function projectAssignments()
    {
        return $this->hasMany(UserProjectRole::class);
    }
	
	public function projects()
	{
		return $this->belongsToMany(Project::class, 'project_user')->withTimestamps();
	}
	
	// TAMBAHKAN: Relasi one-to-one ke Personnel
    public function personnel()
    {
        return $this->hasOne(Personnel::class);
    }
	
	
	/**
     * Mendapatkan semua peran yang dimiliki pengguna di berbagai proyek.
     * Ini adalah relasi yang menghubungkan User ke tabel user_project_roles.
     */
    public function projectRoles()
    {
        return $this->hasMany(UserProjectRole::class);
    }
	
	/**
	 * Mendapatkan peran (role) pengguna untuk proyek yang spesifik.
	 *
	 * @param int $projectId ID dari proyek yang ingin diperiksa.
	 * @return string|null Nama peran (misal: 'kontraktor', 'mk') atau null jika tidak ditemukan.
	 */
	public function getRoleInProject($projectId)
	{
		// Cari peran pengguna di dalam tabel user_project_roles
		$roleRecord = $this->projectRoles()->where('project_id', $projectId)->first();

		// Jika ditemukan, kembalikan nama perannya. Jika tidak, kembalikan null.
		return $roleRecord ? $roleRecord->role : null;
	}
}