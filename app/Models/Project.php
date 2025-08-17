<?php
// GANTI file Project.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    // GANTI: Tambahkan kolom baru
    protected $fillable = [
        'name', 
        'location', 
        'owner_company_id',
        'start_date',
        'end_date',
        'land_area',
        'building_area',
        'floor_count',
    ];

    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    public function userAssignments()
    {
        return $this->hasMany(UserProjectRole::class);
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'project_companies')
                    ->withPivot('role_in_project', 'contract_number', 'contract_value', 'contract_date', 'start_date_contract', 'end_date_contract')
                    ->withTimestamps();
    }
	
	public function users()
	{
		return $this->belongsToMany(User::class, 'project_user')->withTimestamps();
	}

    // TAMBAHKAN: Relasi baru ke personnel
    public function personnel()
    {
        return $this->belongsToMany(Personnel::class, 'project_personnel');
    }
}