<?php

namespace App\Services\Llm;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class ChatCompletionClient
{
    /**
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        $baseUrl = config('llm.base_url');
        $apiKey = config('llm.api_key');
        $timeoutSeconds = (float) config('llm.timeout_seconds', 3.0);

        $request = Http::timeout($timeoutSeconds)
            ->acceptJson()
            ->asJson();

        if (is_string($apiKey) && $apiKey !== '') {
            $request = $request->withToken($apiKey);
        }

        try {
            $response = $request->post($baseUrl.'/chat/completions', $payload);
            $response->throw();
            return (array) $response->json();
        } catch (ConnectionException|RequestException $e) {
            throw $e;
        }
    }

    public function extractMessageContent(array $response): ?string
    {
        $content = $response['choices'][0]['message']['content'] ?? null;
        return is_string($content) ? $content : null;
    }
}

