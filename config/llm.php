<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LLM integration (Grok / LLaMA / OpenAI-compatible)
    |--------------------------------------------------------------------------
    |
    | This project can optionally use an external LLM to generate richer
    | recommendation explanations (and optionally the score itself).
    |
    | Providers like xAI (Grok) and local Ollama can expose OpenAI-compatible
    | endpoints. Configure the base URL + API key + model in your .env.
    */

    'enabled' => (bool) env('LLM_ENABLED', false),

    // Use an OpenAI-compatible base URL that contains /v1 (recommended).
    // Examples:
    // - Ollama: http://127.0.0.1:11434/v1
    // - xAI:    https://api.x.ai/v1
    'base_url' => rtrim((string) env('LLM_BASE_URL', 'http://127.0.0.1:11434/v1'), '/'),

    // Not required for local Ollama. Required for hosted providers.
    'api_key' => (string) env('LLM_API_KEY', ''),

    'model' => (string) env('LLM_MODEL', 'llama3.1'),

    // "rules" => deterministic scoring (conflicts * 20), AI writes the message
    // "ai"    => AI returns the score too (still clamped 0..100)
    'scoring_mode' => (string) env('LLM_SCORING_MODE', 'rules'),

    'temperature' => (float) env('LLM_TEMPERATURE', 0.2),
    'max_tokens' => (int) env('LLM_MAX_TOKENS', 240),
    'timeout_seconds' => (float) env('LLM_TIMEOUT_SECONDS', 3.0),
];

