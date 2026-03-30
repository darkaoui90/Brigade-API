<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Plat;
use App\Models\Plate;
use App\Policies\CategoryPolicy;
use App\Policies\IngredientPolicy;
use App\Policies\PlatPolicy;

class AuthServiceProvider extends ServiceProvider
{

    protected $policies = [
        Category::class => CategoryPolicy::class,
        Ingredient::class => IngredientPolicy::class,
        Plat::class => PlatPolicy::class,
        Plate::class => PlatPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
