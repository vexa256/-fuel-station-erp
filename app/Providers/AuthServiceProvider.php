<?php

namespace App\Providers;

use App\Providers\QueryBuilderUserProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];
    
    public function boot(): void
    {
        // Register custom user provider
        Auth::provider('query_builder', function ($app, array $config) {
            return new QueryBuilderUserProvider();
        });
    }
}
