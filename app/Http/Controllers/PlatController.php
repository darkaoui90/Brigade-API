<?php

namespace App\Http\Controllers;

use App\Models\Plat;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlatController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Plat::class, 'plat');
    }

    public function index(Request $request)
    {
        $plats = $request->user()
            ->plats()
            ->orderBy('name')
            ->get();

        return response()->json($plats);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', $request->user()->id)),
            ],
        ]);

        $plat = $request->user()->plats()->create($validated);

        return response()->json($plat, 201);
    }

    public function show(Plat $plat)
    {
        return response()->json($plat);
    }

    public function update(Request $request, Plat $plat)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'category_id' => [
                'sometimes',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', $request->user()->id)),
            ],
        ]);

        $plat->update($validated);

        return response()->json($plat);
    }

    public function destroy(Plat $plat)
    {
        $plat->delete();

        return response()->json([
            'message' => 'Plat deleted successfully',
        ]);
    }
}
