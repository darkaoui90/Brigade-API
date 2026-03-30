<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DietaryProfileController
{
    public function show(Request $request)
    {
        $profile = $request->user()->profile()->firstOrCreate([], [
            'dietary_tags' => [],
        ]);

        return response()->json($profile);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'dietary_tags' => ['required', 'array'],
            'dietary_tags.*' => [
                'string',
                'distinct',
                Rule::in(['vegan', 'no_sugar', 'no_cholesterol', 'gluten_free', 'no_lactose']),
            ],
        ]);

        $profile = $request->user()->profile()->updateOrCreate(
            [],
            ['dietary_tags' => array_values($validated['dietary_tags'])]
        );

        return response()->json($profile);
    }
}

