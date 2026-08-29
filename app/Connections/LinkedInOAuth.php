<?php

namespace App\Connections;

use App\Enums\SocialPlatform;
use App\Models\PlatformCredential;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class LinkedInOAuth
{
    private const AUTHORIZE_URL = 'https://www.linkedin.com/oauth/v2/authorization';

    private const TOKEN_URL = 'https://www.linkedin.com/oauth/v2/accessToken';

    private const USERINFO_URL = 'https://api.linkedin.com/v2/userinfo';

    private const REVOKE_URL = 'https://www.linkedin.com/oauth/v2/revoke';

    /**
     * @var list<string>
     */
    private const SCOPES = ['openid', 'profile', 'email', 'w_member_social'];

    /**
     * The LinkedIn consent screen URL to send the user to.
     */
    public function redirectUrl(string $state): string
    {
        return self::AUTHORIZE_URL.'?'.http_build_query([
            'response_type' => 'code',
            'client_id' => $this->clientId(),
            'redirect_uri' => $this->redirectUri(),
            'state' => $state,
            'scope' => implode(' ', self::SCOPES),
        ]);
    }

    /**
     * Exchange the authorization code for tokens and fetch the connected profile.
     *
     * @return array{external_id: string, display_name: string, avatar_url: string|null, access_token: string, refresh_token: string|null, expires_at: Carbon|null, scopes: string}
     */
    public function connect(string $code): array
    {
        $token = (array) Http::asForm()->post(self::TOKEN_URL, [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri(),
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
        ])->throw()->json();

        $accessToken = (string) ($token['access_token'] ?? '');

        $profile = (array) Http::withToken($accessToken)->get(self::USERINFO_URL)->throw()->json();

        return [
            'external_id' => (string) ($profile['sub'] ?? ''),
            'display_name' => (string) ($profile['name'] ?? 'LinkedIn account'),
            'avatar_url' => isset($profile['picture']) ? (string) $profile['picture'] : null,
            'access_token' => $accessToken,
            'refresh_token' => isset($token['refresh_token']) ? (string) $token['refresh_token'] : null,
            'expires_at' => isset($token['expires_in']) ? Carbon::now()->addSeconds((int) $token['expires_in']) : null,
            'scopes' => implode(' ', self::SCOPES),
        ];
    }

    /**
     * Revoke an access token at LinkedIn. Throws on failure (the caller decides).
     */
    public function revoke(string $token): void
    {
        Http::asForm()->post(self::REVOKE_URL, [
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'token' => $token,
        ])->throw();
    }

    /**
     * Check whether a client id/secret pair is accepted by LinkedIn.
     *
     * @return array{passed: bool, message: string}
     */
    public function testCredentials(string $clientId, string $clientSecret): array
    {
        if ($clientId === '' || $clientSecret === '') {
            return ['passed' => false, 'message' => 'Client id and secret are both required.'];
        }

        $response = Http::asForm()->post(self::TOKEN_URL, [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if ($response->successful() && $response->json('access_token') !== null) {
            return ['passed' => true, 'message' => 'LinkedIn issued a token — credentials are valid.'];
        }

        $error = (string) $response->json('error', '');

        if ($error === 'invalid_client') {
            return ['passed' => false, 'message' => 'LinkedIn rejected the client id or secret (invalid_client).'];
        }

        // Any non-"invalid_client" response means LinkedIn authenticated the app.
        return [
            'passed' => true,
            'message' => 'LinkedIn recognised the credentials'.($error !== '' ? " ({$error})" : '').'.',
        ];
    }

    private function credential(): ?PlatformCredential
    {
        return PlatformCredential::where('platform', SocialPlatform::Linkedin->value)->first();
    }

    private function clientId(): string
    {
        $credential = $this->credential();

        return $credential ? $credential->client_id : (string) config('services.linkedin.client_id');
    }

    private function clientSecret(): string
    {
        $credential = $this->credential();

        return $credential ? $credential->client_secret : (string) config('services.linkedin.client_secret');
    }

    private function redirectUri(): string
    {
        $credential = $this->credential();
        $configured = (string) config('services.linkedin.redirect');

        return $credential ? ($credential->redirect_url ?? $configured) : $configured;
    }
}
