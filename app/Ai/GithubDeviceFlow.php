<?php

namespace App\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

/**
 * Runs GitHub's OAuth device authorization flow — the same one VS Code uses — to obtain a
 * long-lived GitHub OAuth token that {@see CopilotToken} later exchanges for Copilot API tokens.
 */
class GithubDeviceFlow
{
    private const DEVICE_CODE_URL = 'https://github.com/login/device/code';

    private const ACCESS_TOKEN_URL = 'https://github.com/login/oauth/access_token';

    private const GRANT_TYPE = 'urn:ietf:params:oauth:grant-type:device_code';

    /**
     * Begin the flow and return the code the user must enter at the verification URL.
     *
     * @return array{device_code: string, user_code: string, verification_uri: string, interval: int, expires_in: int}
     *
     * @throws ProviderRequestException
     */
    public function requestCode(): array
    {
        try {
            $response = Http::asForm()
                ->acceptJson()
                ->withHeaders(CopilotIdentity::headers())
                ->timeout(15)
                ->post(self::DEVICE_CODE_URL, [
                    'client_id' => CopilotIdentity::clientId(),
                    'scope' => 'read:user',
                ]);
        } catch (ConnectionException $e) {
            throw new ProviderRequestException('Could not reach GitHub to start the device login.', previous: $e);
        }

        if (! $response->successful()) {
            throw new ProviderRequestException("GitHub returned HTTP {$response->status()} starting the device login.");
        }

        $deviceCode = $response->json('device_code');
        $userCode = $response->json('user_code');
        $verificationUri = $response->json('verification_uri');

        if (! is_string($deviceCode) || ! is_string($userCode) || ! is_string($verificationUri)) {
            throw new ProviderRequestException('GitHub returned an unexpected device-login response.');
        }

        return [
            'device_code' => $deviceCode,
            'user_code' => $userCode,
            'verification_uri' => $verificationUri,
            'interval' => (int) ($response->json('interval') ?? 5),
            'expires_in' => (int) ($response->json('expires_in') ?? 900),
        ];
    }

    /**
     * Poll GitHub until the user authorizes the device, then return the OAuth access token.
     *
     * @throws ProviderRequestException
     */
    public function pollForToken(string $deviceCode, int $interval, int $expiresIn): string
    {
        $interval = max(1, $interval);
        $attempts = (int) ceil($expiresIn / $interval);

        for ($attempt = 0; $attempt < $attempts; $attempt++) {
            Sleep::for($interval)->seconds();

            try {
                $response = Http::asForm()
                    ->acceptJson()
                    ->withHeaders(CopilotIdentity::headers())
                    ->timeout(15)
                    ->post(self::ACCESS_TOKEN_URL, [
                        'client_id' => CopilotIdentity::clientId(),
                        'device_code' => $deviceCode,
                        'grant_type' => self::GRANT_TYPE,
                    ]);
            } catch (ConnectionException $e) {
                throw new ProviderRequestException('Could not reach GitHub while waiting for authorization.', previous: $e);
            }

            $accessToken = $response->json('access_token');

            if (is_string($accessToken) && $accessToken !== '') {
                return $accessToken;
            }

            $interval = $this->handlePending($response->json('error'), $interval);
        }

        throw new ProviderRequestException('Timed out waiting for GitHub authorization. Please try again.');
    }

    /**
     * Keep polling (returning a possibly slower interval) or abort on a terminal error.
     *
     * @throws ProviderRequestException
     */
    private function handlePending(mixed $error, int $interval): int
    {
        return match ($error) {
            'authorization_pending' => $interval,
            'slow_down' => $interval + 5,
            'expired_token' => throw new ProviderRequestException('The device code expired before you authorized it. Please try again.'),
            'access_denied' => throw new ProviderRequestException('Authorization was denied on GitHub.'),
            default => throw new ProviderRequestException('GitHub device login failed'.(is_string($error) && $error !== '' ? ": {$error}." : '.')),
        };
    }
}
