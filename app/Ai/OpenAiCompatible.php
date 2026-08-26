<?php

namespace App\Ai;

use App\Models\AiProvider;

final class OpenAiCompatible
{
    /**
     * Default API base URLs for known OpenAI-compatible drivers.
     *
     * @var array<string, string>
     */
    private const DEFAULT_BASE_URLS = [
        'openai' => 'https://api.openai.com/v1',
        'openrouter' => 'https://openrouter.ai/api/v1',
        'github' => 'https://models.github.ai/inference',
    ];

    /**
     * Resolve the provider's API base URL (its own, or a per-driver default).
     *
     * @throws ProviderRequestException when none is configured
     */
    public static function baseUrl(AiProvider $provider): string
    {
        $baseUrl = rtrim($provider->base_url ?: (self::DEFAULT_BASE_URLS[$provider->driver] ?? ''), '/');

        if ($baseUrl === '') {
            throw new ProviderRequestException('No base URL is configured for this provider.');
        }

        return $baseUrl;
    }
}
