<?php

use App\Ai\ModelCatalog;
use App\Models\AiProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

test('ai:provider:copilot signs in via the device flow and stores the token encrypted', function () {
    Sleep::fake();

    Http::fake([
        'https://github.com/login/device/code' => Http::response([
            'device_code' => 'dev-123',
            'user_code' => 'ABCD-1234',
            'verification_uri' => 'https://github.com/login/device',
            'interval' => 1,
            'expires_in' => 900,
        ]),
        'https://github.com/login/oauth/access_token' => Http::sequence()
            ->push(['error' => 'authorization_pending'])
            ->push(['access_token' => 'gho_secret_oauth']),
    ]);

    $this->artisan('ai:provider:copilot', ['--name' => 'GitHub Copilot'])
        ->expectsOutputToContain('ABCD-1234')
        ->expectsOutputToContain('https://github.com/login/device')
        ->doesntExpectOutputToContain('gho_secret_oauth')
        ->assertSuccessful();

    $provider = AiProvider::where('slug', 'github-copilot')->sole();

    expect($provider->driver)->toBe('copilot')
        ->and($provider->base_url)->toBeNull()
        ->and($provider->api_key)->toBe('gho_secret_oauth');

    $raw = DB::table('ai_providers')->where('id', $provider->id)->value('api_key');
    expect($raw)->not->toBe('gho_secret_oauth');
});

test('ai:provider:copilot fails when authorization is denied and creates nothing', function () {
    Sleep::fake();

    Http::fake([
        'https://github.com/login/device/code' => Http::response([
            'device_code' => 'dev-123',
            'user_code' => 'ABCD-1234',
            'verification_uri' => 'https://github.com/login/device',
            'interval' => 1,
            'expires_in' => 900,
        ]),
        'https://github.com/login/oauth/access_token' => Http::response(['error' => 'access_denied']),
    ]);

    $this->artisan('ai:provider:copilot')->assertFailed();

    expect(AiProvider::count())->toBe(0);
});

test('ai:provider:copilot rejects a duplicate slug before starting the flow', function () {
    AiProvider::factory()->create(['slug' => 'github-copilot']);
    Http::fake();

    $this->artisan('ai:provider:copilot', ['--name' => 'GitHub Copilot'])->assertFailed();

    Http::assertNothingSent();
});

test('a copilot provider lists models through an exchanged token with editor headers', function () {
    Http::fake([
        'https://api.github.com/copilot_internal/v2/token' => Http::response([
            'token' => 'copilot-short-lived',
            'expires_at' => time() + 1800,
        ]),
        'https://api.githubcopilot.com/models' => Http::response([
            'data' => [['id' => 'gpt-4o'], ['id' => 'o3-mini']],
        ]),
    ]);

    $provider = AiProvider::factory()->create([
        'driver' => 'copilot',
        'base_url' => null,
        'api_key' => 'gho_oauth',
    ]);

    $models = app(ModelCatalog::class)->models($provider);

    expect($models)->toBe(['gpt-4o', 'o3-mini']);

    Http::assertSent(fn ($request) => $request->url() === 'https://api.githubcopilot.com/models'
        && $request->hasHeader('Authorization', 'Bearer copilot-short-lived')
        && $request->hasHeader('Copilot-Integration-Id', 'vscode-chat'));
});

test('the exchanged copilot token is cached across calls', function () {
    Http::fake([
        'https://api.github.com/copilot_internal/v2/token' => Http::response([
            'token' => 'copilot-short-lived',
            'expires_at' => time() + 1800,
        ]),
        'https://api.githubcopilot.com/models' => Http::response(['data' => [['id' => 'gpt-4o']]]),
    ]);

    $provider = AiProvider::factory()->create([
        'driver' => 'copilot',
        'base_url' => null,
        'api_key' => 'gho_oauth',
    ]);

    $catalog = app(ModelCatalog::class);
    $catalog->models($provider);
    $catalog->models($provider);

    // One token exchange + two model listings — the token is not re-exchanged.
    Http::assertSentCount(3);
});
