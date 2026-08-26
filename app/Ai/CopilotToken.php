<?php

namespace App\Ai;

use App\Models\AiProvider;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Exchanges a provider's stored GitHub OAuth token for the short-lived token the Copilot API
 * needs, caching it per provider until shortly before it expires.
 */
class CopilotToken
{
    private const TOKEN_EXCHANGE_URL = 'https://api.github.com/copilot_internal/v2/token';

    public function __construct(private readonly Cache $cache) {}

    /**
     * Return a valid Copilot API token for the provider, exchanging the stored OAuth token when
     * the cached one is missing or expired.
     *
     * @throws ProviderRequestException
     */
    public function forProvider(AiProvider $provider): string
    {
        $cacheKey = "copilot-token:{$provider->getKey()}";
        $cached = $this->cache->get($cacheKey);

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        [$token, $expiresAt] = $this->exchange((string) $provider->api_key);

        // Refresh a minute before GitHub expires it; never cache for under a minute.
        $this->cache->put($cacheKey, $token, max(60, $expiresAt - time() - 60));

        return $token;
    }

    /**
     * @return array{0: string, 1: int} the Copilot token and its unix expiry
     *
     * @throws ProviderRequestException
     */
    private function exchange(string $oauthToken): array
    {
        if ($oauthToken === '') {
            throw new ProviderRequestException('This Copilot provider is not signed in — run "php artisan ai:provider:copilot" again.');
        }

        try {
            $response = Http::withHeaders(CopilotIdentity::headers() + ['Authorization' => "token {$oauthToken}"])
                ->acceptJson()
                ->timeout(15)
                ->get(self::TOKEN_EXCHANGE_URL);
        } catch (ConnectionException $e) {
            throw new ProviderRequestException('Could not reach GitHub to authorize Copilot.', previous: $e);
        }

        if (in_array($response->status(), [401, 403], true)) {
            throw new ProviderRequestException('GitHub rejected the Copilot sign-in — run "php artisan ai:provider:copilot" again.');
        }

        if (! $response->successful()) {
            throw new ProviderRequestException("GitHub returned HTTP {$response->status()} while authorizing Copilot.");
        }

        $token = $response->json('token');

        if (! is_string($token) || $token === '') {
            throw new ProviderRequestException('GitHub did not return a Copilot token.');
        }

        $expiresAt = $response->json('expires_at');

        return [$token, is_int($expiresAt) ? $expiresAt : time() + 300];
    }
}
