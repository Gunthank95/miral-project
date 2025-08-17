<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProjectRole extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit jika nama model berbeda dari standar
    protected $table = 'user_project_roles';

    /**
     * Mendefinisikan relasi bahwa sebuah penugasan "milik" seorang User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendefinisikan relasi bahwa sebuah penugasan "milik" sebuah Project.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'user_id',
		'project_id',
		'package_id',
		'role',
	];
}