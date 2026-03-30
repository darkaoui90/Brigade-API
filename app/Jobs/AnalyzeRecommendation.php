<?php

namespace App\Jobs;

use App\Models\Recommendation;
use App\Services\Llm\RecommendationAi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class AnalyzeRecommendation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [1, 3, 5];

    public function __construct(public int $recommendationId)
    {
    }

    public function handle(): void
    {
        $recommendation = Recommendation::query()
            ->with(['user.profile', 'plate.ingredients'])
            ->findOrFail($this->recommendationId);

        $dietaryTags = $recommendation->user->profile?->dietary_tags ?? [];
        $dietaryTags = is_array($dietaryTags) ? array_values(array_unique($dietaryTags)) : [];

        $ingredientTags = $recommendation->plate->ingredients
            ->flatMap(fn ($ingredient) => $ingredient->tags ?? [])
            ->filter()
            ->unique()
            ->values()
            ->all();

        $rules = [
            'vegan' => 'contains_meat',
            'no_sugar' => 'contains_sugar',
            'no_cholesterol' => 'contains_cholesterol',
            'gluten_free' => 'contains_gluten',
            'no_lactose' => 'contains_lactose',
        ];

        $conflicts = [];
        foreach ($dietaryTags as $dietaryTag) {
            $conflictingIngredientTag = $rules[$dietaryTag] ?? null;
            if (!$conflictingIngredientTag) {
                continue;
            }

            if (in_array($conflictingIngredientTag, $ingredientTags, true)) {
                $conflicts[] = [
                    'dietary_tag' => $dietaryTag,
                    'ingredient_tag' => $conflictingIngredientTag,
                ];
            }
        }

        $ruleScore = 100 - (count($conflicts) * 20);
        $ruleScore = max(0, min(100, $ruleScore));

        $plateContext = [
            'name' => $recommendation->plate->name,
            'description' => $recommendation->plate->description,
            'ingredients' => $recommendation->plate->ingredients->pluck('name')->values()->all(),
        ];

        /** @var RecommendationAi $ai */
        $ai = app(RecommendationAi::class);
        $aiResult = $ai->analyze($dietaryTags, $ingredientTags, $plateContext);

        $score = $ruleScore;
        if (config('llm.enabled') && config('llm.scoring_mode') === 'ai') {
            $aiScore = Arr::get($aiResult ?? [], 'score');
            if (is_numeric($aiScore)) {
                $score = max(0, min(100, (float) $aiScore));
            }
        }

        $label = match (true) {
            $score >= 80 => 'Highly Recommended',
            $score >= 50 => 'Recommended with notes',
            default => 'Not Recommended',
        };

        $warningMessage = Arr::get($aiResult ?? [], 'warning_message');
        if (!is_string($warningMessage) || $warningMessage === '') {
            $warningMessage = null;
            if (count($conflicts) > 0) {
                $pairs = collect($conflicts)
                    ->map(fn ($c) => "{$c['dietary_tag']} ({$c['ingredient_tag']})")
                    ->implode(', ');

                $warningMessage = $score < 50
                    ? "Not compatible with your dietary profile: {$pairs}."
                    : "Notes: {$pairs}.";
            }
        }

        $recommendation->update([
            'status' => 'ready',
            'score' => $score,
            'label' => $label,
            'warning_message' => $warningMessage,
        ]);
    }
}
