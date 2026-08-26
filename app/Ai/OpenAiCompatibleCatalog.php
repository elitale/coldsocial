<?php

namespace App\Ai;

use App\Models\AiProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class OpenAiCompatibleCatalog implements ListsModels
{
    /**
     * Default `/models` endpoints for known OpenAI-compatible drivers.
     *
     * @var array<string, string>
     */
    private const DEFAULT_BASE_URLS = [
        'openai' => 'https://api.openai.com/v1',
        'openrouter' => 'https://openrouter.ai/api/v1',
        'github' => 'https://models.github.ai/inference',
    ];

    public function models(AiProvider $provider): array
    {
        $baseUrl = rtrim($provider->base_url ?: (self::DEFAULT_BASE_URLS[$provider->driver] ?? ''), '/');

        if ($baseUrl === '') {
            throw new ProviderRequestException('No base URL is configured for this provider.');
        }

        try {
            $response = Http::withToken((string) $provider->api_key)
                ->acceptJson()
                ->timeout(15)
                ->get("{$baseUrl}/models");
        } catch (ConnectionException $e) {
            throw new ProviderRequestException("Could not reach {$baseUrl}.", previous: $e);
        }

        if (in_array($response->status(), [401, 403], true)) {
            throw new ProviderRequestException('The API key was rejected by the provider.');
        }

        if (! $response->successful()) {
            throw new ProviderRequestException("The provider returned HTTP {$response->status()}.");
        }

        /** @var array<int, mixed> $data */
        $data = $response->json('data') ?? $response->json('models') ?? [];

        $identifiers = [];

        foreach ($data as $model) {
            $id = match (true) {
                is_string($model) => $model,
                is_array($model) => $this->firstStringKey($model, ['id', 'name']),
                default => null,
            };

            if (is_string($id) && $id !== '') {
                $identifiers[] = $id;
            }
        }

        return array_values(array_unique($identifiers));
    }

    /**
     * @param  array<mixed>  $model
     * @param  list<string>  $keys
     */
    private function firstStringKey(array $model, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (is_string($model[$key] ?? null)) {
                return $model[$key];
            }
        }

        return null;
    }
}
