<?php

namespace App\Providers;

use App\Models\Project; // TAMBAHKAN
use App\Policies\ProjectPolicy; // TAMBAHKAN
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Document;
use App\Policies\DocumentPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
		// 'App\Models\Model' => 'App\Policies\ModelPolicy',
		Document::class => DocumentPolicy::class, // Daftarkan policy baru kita di sini
	];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}