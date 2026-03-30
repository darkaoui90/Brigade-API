<?php

namespace App\Http\Controllers;

use App\Models\Plate;
use App\Models\Recommendation;
use Illuminate\Http\Request;

class PlateController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Plate::class, 'plate');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $platesQuery = Plate::query()
            ->with(['category', 'ingredients'])
            ->orderBy('name');

        if (!$user->is_admin) {
            $platesQuery
                ->where('is_available', true)
                ->whereHas('category', fn ($q) => $q->where('is_active', true));
        }

        $plates = $platesQuery->paginate((int) $request->query('per_page', 15));

        $recommendations = Recommendation::query()
            ->where('user_id', $user->id)
            ->whereIn('plate_id', collect($plates->items())->pluck('id'))
            ->get()
            ->keyBy('plate_id');

        $plates->through(function (Plate $plate) use ($recommendations) {
            return [
                ...$plate->toArray(),
                'recommendation' => $this->recommendationPayload($recommendations->get($plate->id)),
            ];
        });

        return response()->json($plates);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'string'],
            'is_available' => ['sometimes', 'boolean'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'ingredient_ids' => ['sometimes', 'array'],
            'ingredient_ids.*' => ['integer', 'distinct', 'exists:ingredients,id'],
        ]);

        $plate = Plate::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'image' => $validated['image'] ?? null,
            'is_available' => $validated['is_available'] ?? true,
            'category_id' => $validated['category_id'],
            'user_id' => $request->user()->id,
        ]);

        if (array_key_exists('ingredient_ids', $validated)) {
            $plate->ingredients()->sync($validated['ingredient_ids']);
        }

        return response()->json($plate->load(['category', 'ingredients']), 201);
    }

    public function show(Request $request, Plate $plate)
    {
        $plate->load(['category', 'ingredients']);

        $recommendation = Recommendation::query()
            ->where('user_id', $request->user()->id)
            ->where('plate_id', $plate->id)
            ->first();

        return response()->json([
            ...$plate->toArray(),
            'recommendation' => $this->recommendationPayload($recommendation),
        ]);
    }

    public function update(Request $request, Plate $plate)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'image' => ['nullable', 'string'],
            'is_available' => ['sometimes', 'boolean'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'ingredient_ids' => ['sometimes', 'array'],
            'ingredient_ids.*' => ['integer', 'distinct', 'exists:ingredients,id'],
        ]);

        $plate->fill($validated);
        $plate->save();

        if (array_key_exists('ingredient_ids', $validated)) {
            $plate->ingredients()->sync($validated['ingredient_ids']);
        }

        return response()->json($plate->load(['category', 'ingredients']));
    }

    public function destroy(Plate $plate)
    {
        $plate->delete();

        return response()->json([
            'message' => 'Plate deleted successfully',
        ]);
    }

    private function recommendationPayload(?Recommendation $recommendation): ?array
    {
        if (!$recommendation) {
            return null;
        }

        return [
            'status' => $recommendation->status,
            'score' => $recommendation->score,
            'label' => $recommendation->label,
            'warning_message' => $recommendation->warning_message,
            'updated_at' => $recommendation->updated_at,
        ];
    }
}
