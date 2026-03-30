<?php

namespace App\Http\Controllers;

use App\Jobs\AnalyzeRecommendation;
use App\Models\Plate;
use App\Models\Recommendation;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function analyze(Request $request, Plate $plate)
    {
        $this->authorize('view', $plate);

        $user = $request->user();

        $recommendation = Recommendation::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'plate_id' => $plate->id,
            ],
            [
                'status' => 'processing',
                'score' => null,
                'label' => null,
                'warning_message' => null,
            ]
        );

        AnalyzeRecommendation::dispatch($recommendation->id);

        return response()->json($recommendation->fresh(), 202);
    }

    public function index(Request $request)
    {
        $recommendations = Recommendation::query()
            ->where('user_id', $request->user()->id)
            ->with(['plate.category'])
            ->orderByDesc('updated_at')
            ->paginate((int) $request->query('per_page', 15));

        return response()->json($recommendations);
    }

    public function show(Request $request, Plate $plate)
    {
        $this->authorize('view', $plate);

        $recommendation = Recommendation::query()
            ->where('user_id', $request->user()->id)
            ->where('plate_id', $plate->id)
            ->first();

        if (!$recommendation) {
            return response()->json([
                'message' => 'No recommendation found for this plate.',
            ], 404);
        }

        return response()->json($recommendation->load(['plate.category', 'plate.ingredients']));
    }
}

