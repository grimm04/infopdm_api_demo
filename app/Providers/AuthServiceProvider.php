<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate; 
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Route;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Models\Model' => 'App\Policies\ModelPolicy',   
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Passport::routes(null, array('prefix' => 'oauth', 'middleware'  =>  array('cors', 'json.response'), 'excluded_middleware'=>'api'));// auth:api','
        Passport::tokensExpireIn(\Carbon\Carbon::now()->addHours(24));
        Passport::refreshTokensExpireIn(\Carbon\Carbon::now()->addDays(30)); 
    }
}
