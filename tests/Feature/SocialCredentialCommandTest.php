<?php

use App\Connections\LinkedInOAuth;
use App\Enums\SocialPlatform;
use App\Models\PlatformCredential;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

test('social:credential:set stores an encrypted secret and never prints it', function () {
    $this->artisan('social:credential:set', [
        '--platform' => 'linkedin',
        '--client-id' => 'client-abc',
        '--client-secret' => 'super-secret',
        '--redirect' => 'https://coldsocial.test/connections/linkedin/callback',
    ])->assertSuccessful()
        ->doesntExpectOutputToContain('super-secret');

    $credential = PlatformCredential::where('platform', SocialPlatform::Linkedin)->sole();

    expect($credential->client_id)->toBe('client-abc')
        ->and($credential->client_secret)->toBe('super-secret')
        ->and($credential->redirect_url)->toBe('https://coldsocial.test/connections/linkedin/callback');

    $raw = DB::table('platform_credentials')->where('id', $credential->id)->value('client_secret');
    expect($raw)->not->toBe('super-secret');
});

test('social:credential:set updates existing credentials in place', function () {
    PlatformCredential::factory()->create([
        'platform' => SocialPlatform::Linkedin,
        'client_id' => 'old-id',
    ]);

    $this->artisan('social:credential:set', [
        '--platform' => 'linkedin',
        '--client-id' => 'new-id',
        '--client-secret' => 'new-secret',
    ])->assertSuccessful();

    expect(PlatformCredential::where('platform', SocialPlatform::Linkedin)->count())->toBe(1)
        ->and(PlatformCredential::sole()->client_id)->toBe('new-id');
});

test('social:credential:set rejects a non-connectable platform', function () {
    $this->artisan('social:credential:set', [
        '--platform' => 'instagram',
        '--client-id' => 'x',
        '--client-secret' => 'y',
    ])->assertFailed();

    expect(PlatformCredential::count())->toBe(0);
});

test('social:credential:set can test right after saving', function () {
    Http::fake(['*/accessToken' => Http::response(['access_token' => 'tok'], 200)]);

    $this->artisan('social:credential:set', [
        '--platform' => 'linkedin',
        '--client-id' => 'id',
        '--client-secret' => 'secret',
        '--test' => true,
    ])->assertSuccessful();

    $credential = PlatformCredential::sole();

    expect($credential->test_passed)->toBeTrue()
        ->and($credential->last_tested_at)->not->toBeNull();
});

test('social:credential:test passes when LinkedIn issues a token', function () {
    Http::fake(['*/accessToken' => Http::response(['access_token' => 'tok'], 200)]);
    PlatformCredential::factory()->create(['platform' => SocialPlatform::Linkedin]);

    $this->artisan('social:credential:test', ['platform' => 'linkedin'])->assertSuccessful();

    expect(PlatformCredential::sole()->test_passed)->toBeTrue();
});

test('social:credential:test fails on invalid_client', function () {
    Http::fake(['*/accessToken' => Http::response(['error' => 'invalid_client'], 401)]);
    PlatformCredential::factory()->create(['platform' => SocialPlatform::Linkedin]);

    $this->artisan('social:credential:test', ['platform' => 'linkedin'])->assertFailed();

    $credential = PlatformCredential::sole();

    expect($credential->test_passed)->toBeFalse()
        ->and($credential->test_message)->toContain('invalid_client');
});

test('social:credential:test treats a recognised client as valid', function () {
    Http::fake(['*/accessToken' => Http::response(['error' => 'unauthorized_client'], 403)]);
    PlatformCredential::factory()->create(['platform' => SocialPlatform::Linkedin]);

    $this->artisan('social:credential:test', ['platform' => 'linkedin'])->assertSuccessful();

    expect(PlatformCredential::sole()->test_passed)->toBeTrue();
});

test('social:credential:list shows the client id but never the secret', function () {
    PlatformCredential::factory()->create([
        'platform' => SocialPlatform::Linkedin,
        'client_id' => 'visible-client-id',
        'client_secret' => 'hidden-secret',
    ]);

    $this->artisan('social:credential:list')
        ->assertSuccessful()
        ->expectsOutputToContain('visible-client-id')
        ->doesntExpectOutputToContain('hidden-secret');
});

test('social:credential:remove deletes stored credentials', function () {
    PlatformCredential::factory()->create(['platform' => SocialPlatform::Linkedin]);

    $this->artisan('social:credential:remove', ['platform' => 'linkedin', '--force' => true])->assertSuccessful();

    expect(PlatformCredential::count())->toBe(0);
});

test('the connect flow uses stored credentials over config', function () {
    PlatformCredential::factory()->create([
        'platform' => SocialPlatform::Linkedin,
        'client_id' => 'stored-client-id',
        'redirect_url' => 'https://stored.example/callback',
    ]);

    $url = app(LinkedInOAuth::class)->redirectUrl('state123');

    expect($url)->toContain('client_id=stored-client-id')
        ->and($url)->toContain(urlencode('https://stored.example/callback'));
});
