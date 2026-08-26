<?php

namespace App\Ai;

use App\Models\AiProvider;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * The single seam that authenticates an outgoing HTTP request for a provider.
 *
 * Most providers use a static bearer key. GitHub Copilot instead exchanges its stored OAuth token
 * for a short-lived token and adds the editor headers the Copilot API requires. This is the only
 * place that branches on auth style — promote it to a strategy interface once a third style exists.
 */
class ProviderRequest
{
    public function __construct(private readonly CopilotToken $copilotToken) {}

    public function for(AiProvider $provider): PendingRequest
    {
        if ($provider->driver === 'copilot') {
            return Http::withToken($this->copilotToken->forProvider($provider))
                ->withHeaders(CopilotIdentity::apiHeaders())
                ->acceptJson();
        }

        return Http::withToken((string) $provider->api_key)->acceptJson();
    }
}
