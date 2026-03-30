<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IngredientController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Ingredient::class, 'ingredient');
    }

    public function index(Request $request)
    {
        $ingredients = Ingredient::query()
            ->orderBy('name')
            ->paginate((int) $request->query('per_page', 50));

        return response()->json($ingredients);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('ingredients', 'name')],
            'tags' => ['sometimes', 'array'],
            'tags.*' => [
                'string',
                'distinct',
                Rule::in([
                    'contains_meat',
                    'contains_sugar',
                    'contains_cholesterol',
                    'contains_gluten',
                    'contains_lactose',
                ]),
            ],
        ]);

        $ingredient = Ingredient::create([
            'name' => $validated['name'],
            'tags' => array_values($validated['tags'] ?? []),
        ]);

        return response()->json($ingredient, 201);
    }

    public function show(Ingredient $ingredient)
    {
        return response()->json($ingredient);
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('ingredients', 'name')->ignore($ingredient->id)],
            'tags' => ['sometimes', 'array'],
            'tags.*' => [
                'string',
                'distinct',
                Rule::in([
                    'contains_meat',
                    'contains_sugar',
                    'contains_cholesterol',
                    'contains_gluten',
                    'contains_lactose',
                ]),
            ],
        ]);

        $ingredient->fill($validated);

        if (array_key_exists('tags', $validated)) {
            $ingredient->tags = array_values($validated['tags'] ?? []);
        }

        $ingredient->save();

        return response()->json($ingredient);
    }

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();

        return response()->json([
            'message' => 'Ingredient deleted successfully',
        ]);
    }
}

