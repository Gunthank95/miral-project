<?php
// GANTI: app/Models/Company.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
	
	// TAMBAHKAN: Kolom baru di $fillable
	protected $fillable = [
        'name', 
        'type',
        'address',
        'phone_number',
        'email',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function personnel()
    {
        return $this->hasMany(Personnel::class);
    }
}