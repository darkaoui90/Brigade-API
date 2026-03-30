<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Category::class, 'category');
    }

    public function index(Request $request)
    {
        $categoriesQuery = Category::query()->orderBy('name');

        if (!$request->user()->is_admin) {
            $categoriesQuery->where('is_active', true);
        }

        $categories = $categoriesQuery->paginate((int) $request->query('per_page', 15));

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name'),
            ],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $category = Category::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json($category, 201);
    }

    public function show(Category $category)
    {
        return response()->json($category);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')
                    ->ignore($category->id)
            ],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $category->update($validated);

        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }

    public function plates(Request $request, Category $category)
    {
        $this->authorize('view', $category);

        $platesQuery = $category->plates()->with(['ingredients'])->orderBy('name');

        if (!$request->user()->is_admin) {
            $platesQuery->where('is_available', true);
        }

        $plates = $platesQuery->paginate((int) $request->query('per_page', 15));

        return response()->json($plates);
    }

}
