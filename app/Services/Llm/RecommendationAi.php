<?php

namespace App\Services\Llm;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class RecommendationAi
{
    public function __construct(private readonly ChatCompletionClient $client)
    {
    }

    /**
     * @param  array<int, string>  $dietaryTags
     * @param  array<int, string>  $ingredientTags
     * @return array{score: ?float, label: ?string, warning_message: ?string}|null
     */
    public function analyze(array $dietaryTags, array $ingredientTags, array $plateContext): ?array
    {
        if (!config('llm.enabled')) {
            return null;
        }

        $model = (string) config('llm.model');
        $temperature = (float) config('llm.temperature', 0.2);
        $maxTokens = (int) config('llm.max_tokens', 240);

        $system = implode("\n", [
            'You are a nutrition compatibility assistant for a restaurant app.',
            'You must respond with ONLY a valid JSON object (no markdown).',
            'Output schema:',
            '{ "score": number (0-100), "label": string, "warning_message": string|null }',
            'Label must be one of: "Highly Recommended", "Recommended with notes", "Not Recommended".',
        ]);

        $user = [
            'Compute compatibility between the user dietary tags and ingredient tags.',
            'Dietary tags are restrictions:',
            '- vegan, no_sugar, no_cholesterol, gluten_free, no_lactose',
            'Ingredient tags indicate the plate contains:',
            '- contains_meat, contains_sugar, contains_cholesterol, contains_gluten, contains_lactose',
            '',
            'Plate context:',
            json_encode($plateContext, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            '',
            'User dietary_tags:',
            json_encode(array_values($dietaryTags), JSON_UNESCAPED_UNICODE),
            '',
            'Plate ingredient_tags:',
            json_encode(array_values($ingredientTags), JSON_UNESCAPED_UNICODE),
            '',
            'Scoring rubric:',
            '- Start at 100',
            '- Each conflict reduces 20 points',
            '- Clamp to 0..100',
            '- If score >= 80 => Highly Recommended',
            '- If 50..79 => Recommended with notes',
            '- If < 50 => Not Recommended',
            '',
            'warning_message: short, clear, end-user friendly. If there are no conflicts, set it to null.',
        ];

        $payload = [
            'model' => $model,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => implode("\n", $user)],
            ],
        ];

        try {
            $response = $this->client->create($payload);
        } catch (ConnectionException|RequestException) {
            return null;
        }

        $content = $this->client->extractMessageContent($response);
        if (!$content) {
            return null;
        }

        $json = $this->decodeJsonObject($content);
        if (!$json) {
            return null;
        }

        $score = Arr::get($json, 'score');
        $label = Arr::get($json, 'label');
        $warningMessage = Arr::get($json, 'warning_message');

        $score = is_numeric($score) ? (float) $score : null;
        if ($score !== null) {
            $score = max(0, min(100, $score));
        }

        $label = is_string($label) ? $label : null;
        $warningMessage = is_string($warningMessage) ? $warningMessage : null;

        return [
            'score' => $score,
            'label' => $label,
            'warning_message' => $warningMessage,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeJsonObject(string $content): ?array
    {
        $trimmed = trim($content);
        $decoded = json_decode($trimmed, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // If the model accidentally wraps JSON with text, extract the first object.
        $start = Str::position($trimmed, '{');
        $end = Str::of($trimmed)->rpos('}');
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        $slice = substr($trimmed, $start, $end - $start + 1);
        $decoded = json_decode($slice, true);
        return is_array($decoded) ? $decoded : null;
    }
}

