<?php

namespace App\Ai;

use App\Models\AiProvider;

interface ListsModels
{
    /**
     * Fetch the provider's available model identifiers (also verifies the API key).
     *
     * @return list<string>
     *
     * @throws ProviderRequestException
     */
    public function models(AiProvider $provider): array;
}
