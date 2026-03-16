<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Plat;
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
        $categories = $request->user()
            ->categories()
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->where(fn ($query) => $query->where('user_id', $request->user()->id)),
            ],
        ]);

        $category = $request->user()->categories()->create($validated);

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
                'max:255',
                Rule::unique('categories', 'name')
                    ->ignore($category->id)
                    ->where(fn ($query) => $query->where('user_id', $request->user()->id)),
            ],
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

    public function attachPlats(Request $request, Category $category)
    {
        $this->authorize('update', $category);

        $validated = $request->validate([
            'plat_ids' => ['required', 'array', 'min:1'],
            'plat_ids.*' => ['integer', 'distinct', 'exists:plats,id'],
        ]);

        $platIds = $validated['plat_ids'];

        $plats = Plat::query()
            ->whereIn('id', $platIds)
            ->where('user_id', $request->user()->id)
            ->get();

        if ($plats->count() !== count($platIds)) {
            return response()->json([
                'message' => 'One or more plats do not belong to the authenticated user.',
            ], 422);
        }

        Plat::query()
            ->whereIn('id', $platIds)
            ->update(['category_id' => $category->id]);

        return response()->json([
            'message' => 'Plats attached successfully.',
        ]);
    }

}
