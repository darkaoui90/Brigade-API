<?php

namespace App\Http\Controllers;
use App\Models\Category;


use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255|unique:categories,name'
    ]);

    $category = Category::create([
        'name' => $validated['name'],
        'user_id' => $request->user()->id
    ]);

    return response()->json($category, 201);
}

    public function index(Request $request)
    {
        $categories = Category::query()
            ->where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    public function show(Request $request, $id)
{
    $category = Category::where('id', $id)
        ->where('user_id', $request->user()->id)
        ->first();

    if (!$category) {
        return response()->json([
            'message' => 'Category not found'
        ], 404);
    }

    return response()->json($category);
}

}
