<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use App\Models\Category;
use App\Models\Plat;
use App\Policies\CategoryPolicy;
use App\Policies\PlatPolicy;

class AuthServiceProvider extends ServiceProvider
{

    protected $policies = [
        Category::class => CategoryPolicy::class,
        Plat::class => PlatPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
