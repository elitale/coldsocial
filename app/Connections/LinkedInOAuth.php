<?php

namespace App\Connections;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class LinkedInOAuth
{
    private const AUTHORIZE_URL = 'https://www.linkedin.com/oauth/v2/authorization';

    private const TOKEN_URL = 'https://www.linkedin.com/oauth/v2/accessToken';

    private const USERINFO_URL = 'https://api.linkedin.com/v2/userinfo';

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
            'client_id' => (string) config('services.linkedin.client_id'),
            'redirect_uri' => (string) config('services.linkedin.redirect'),
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
            'redirect_uri' => (string) config('services.linkedin.redirect'),
            'client_id' => (string) config('services.linkedin.client_id'),
            'client_secret' => (string) config('services.linkedin.client_secret'),
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
}
