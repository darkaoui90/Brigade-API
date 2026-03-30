<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Plate;
use App\Models\Recommendation;
use Illuminate\Http\Request;

class AdminStatsController extends Controller
{
    public function __invoke(Request $request)
    {
        abort_unless((bool) $request->user()->is_admin, 403);

        $totalRecommendations = Recommendation::query()->count();
        $readyRecommendations = Recommendation::query()->where('status', 'ready')->count();
        $processingRecommendations = Recommendation::query()->where('status', 'processing')->count();

        return response()->json([
            'categories' => [
                'total' => Category::query()->count(),
                'active' => Category::query()->where('is_active', true)->count(),
            ],
            'plates' => [
                'total' => Plate::query()->count(),
                'available' => Plate::query()->where('is_available', true)->count(),
            ],
            'ingredients' => [
                'total' => Ingredient::query()->count(),
            ],
            'recommendations' => [
                'total' => $totalRecommendations,
                'ready' => $readyRecommendations,
                'processing' => $processingRecommendations,
                'average_score' => $totalRecommendations > 0
                    ? (float) Recommendation::query()->whereNotNull('score')->avg('score')
                    : null,
            ],
        ]);
    }
}

