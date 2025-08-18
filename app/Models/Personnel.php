<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personnel extends Model
{
    use HasFactory;

    // TAMBAHKAN: Baris ini memberitahu Laravel nama tabel yang benar.
    protected $table = 'personnel';

    protected $fillable = [
        'user_id',
        'company_id',
        'name',
        'position',
        'nik',
        'phone_number',
        'email',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_personnel');
    }
	
	// TAMBAHKAN: Relasi one-to-one ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}