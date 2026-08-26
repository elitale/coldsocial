<?php

namespace App\Ai;

use App\Models\AiProvider;

class ModelCatalog
{
    /**
     * Driver keys that speak the OpenAI-compatible `/models` API.
     *
     * @var list<string>
     */
    private const OPENAI_COMPATIBLE = ['openai', 'openrouter', 'github'];

    public function __construct(private readonly OpenAiCompatibleCatalog $openAiCompatible) {}

    /**
     * Whether we can list models for this provider (else the caller falls back to manual entry).
     */
    public function supports(AiProvider $provider): bool
    {
        return in_array($provider->driver, self::OPENAI_COMPATIBLE, true) || filled($provider->base_url);
    }

    /**
     * @return list<string>
     *
     * @throws ProviderRequestException
     */
    public function models(AiProvider $provider): array
    {
        return $this->openAiCompatible->models($provider);
    }
}
