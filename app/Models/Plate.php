<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Plate extends Plat
{
    protected $table = 'plats';

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);

    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'ingredient_plat', 'plat_id', 'ingredient_id')
            ->withTimestamps();
    }
    // public function allergens(): BelongsToMany
    // {
    //     return $this->belongsToMany(Allergen::class, 'allergen_plat', 'plat_id', 'allergen_id')
    // }
}
