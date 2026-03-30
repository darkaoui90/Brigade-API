<?php

namespace App\Http\Controllers;

use App\Jobs\AnalyzeRecommendation;
use App\Models\Category;
use App\Models\Plate;
use App\Models\Recommendation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AppController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if (!$user) {
            return view('app');
        }

        $profile = $user->profile()->firstOrCreate([], ['dietary_tags' => []]);

        $categoriesQuery = Category::query()->orderBy('name');
        if (!$user->is_admin) {
            $categoriesQuery->where('is_active', true);
        }

        $platesQuery = Plate::query()
            ->with(['category', 'ingredients', 'recommendations' => fn ($q) => $q->where('user_id', $user->id)])
            ->orderBy('name');

        if (!$user->is_admin) {
            $platesQuery
                ->where('is_available', true)
                ->whereHas('category', fn ($q) => $q->where('is_active', true));
        }

        return view('app', [
            'dietaryProfile' => $profile,
            'categories' => $categoriesQuery->limit(50)->get(),
            'plates' => $platesQuery->limit(50)->get(),
        ]);
    }

    public function updateDietaryProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'dietary_tags' => ['array'],
            'dietary_tags.*' => ['string', Rule::in(['vegan', 'no_sugar', 'no_cholesterol', 'gluten_free', 'no_lactose'])],
        ]);

        $tags = collect($validated['dietary_tags'] ?? [])
            ->filter()
            ->unique()
            ->values()
            ->all();

        $request->user()->profile()->updateOrCreate([], [
            'dietary_tags' => $tags,
        ]);

        return back()->with('status', 'dietary-profile-updated');
    }

    public function analyze(Request $request, Plate $plate): RedirectResponse
    {
        $this->authorize('view', $plate);

        $recommendation = Recommendation::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
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

        return back()->with('status', 'recommendation-queued');
    }
}
