<?php

use App\Enums\AiCapability;
use App\Models\AiModel;
use App\Models\AiProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

test('ai:provider:add creates a provider with an encrypted key and never prints it', function () {
    $this->artisan('ai:provider:add', [
        '--name' => 'OpenAI',
        '--driver' => 'openai',
        '--key' => 'sk-super-secret',
    ])->assertSuccessful()
        ->doesntExpectOutputToContain('sk-super-secret');

    $provider = AiProvider::where('slug', 'openai')->sole();

    expect($provider->driver)->toBe('openai')
        ->and($provider->api_key)->toBe('sk-super-secret');

    $raw = DB::table('ai_providers')->where('id', $provider->id)->value('api_key');
    expect($raw)->not->toBe('sk-super-secret');
});

test('ai:provider:add rejects a duplicate slug', function () {
    AiProvider::factory()->create(['slug' => 'openai']);

    $this->artisan('ai:provider:add', [
        '--name' => 'OpenAI',
        '--slug' => 'openai',
        '--driver' => 'openai',
        '--key' => 'x',
    ])->assertFailed();
});

test('ai:provider:list shows providers without revealing the key', function () {
    AiProvider::factory()->create([
        'name' => 'OpenAI',
        'slug' => 'openai',
        'api_key' => 'sk-secret',
    ]);

    $this->artisan('ai:provider:list')
        ->assertSuccessful()
        ->expectsOutputToContain('openai')
        ->doesntExpectOutputToContain('sk-secret');
});

test('ai:model:add adds a model to a provider', function () {
    AiProvider::factory()->create(['slug' => 'openai']);

    $this->artisan('ai:model:add', [
        'provider' => 'openai',
        '--identifier' => 'gpt-4o',
        '--capability' => 'text',
        '--default' => true,
    ])->assertSuccessful();

    $model = AiModel::sole();

    expect($model->identifier)->toBe('gpt-4o')
        ->and($model->capability)->toBe(AiCapability::Text)
        ->and($model->is_default)->toBeTrue();
});

test('ai:model:add rejects an invalid capability', function () {
    AiProvider::factory()->create(['slug' => 'openai']);

    $this->artisan('ai:model:add', [
        'provider' => 'openai',
        '--identifier' => 'gpt-4o',
        '--capability' => 'banana',
    ])->assertFailed();

    expect(AiModel::count())->toBe(0);
});

test('ai:model:default sets the default and clears the previous one', function () {
    $provider = AiProvider::factory()->create(['slug' => 'openai']);
    $old = AiModel::factory()->for($provider, 'provider')->capability(AiCapability::Text)->default()->create(['identifier' => 'gpt-4o-mini']);
    $new = AiModel::factory()->for($provider, 'provider')->capability(AiCapability::Text)->create(['identifier' => 'gpt-4o']);

    $this->artisan('ai:model:default', ['capability' => 'text', 'identifier' => 'gpt-4o'])
        ->assertSuccessful();

    expect($new->fresh()->is_default)->toBeTrue()
        ->and($old->fresh()->is_default)->toBeFalse();
});

test('ai:model:default fails for an unknown model', function () {
    $this->artisan('ai:model:default', ['capability' => 'text', 'identifier' => 'nope'])
        ->assertFailed();
});

test('ai:provider:enable and ai:provider:disable flip the flag', function () {
    $provider = AiProvider::factory()->create(['slug' => 'openai', 'enabled' => false]);

    $this->artisan('ai:provider:enable', ['slug' => 'openai'])->assertSuccessful();
    expect($provider->fresh()->enabled)->toBeTrue();

    $this->artisan('ai:provider:disable', ['slug' => 'openai'])->assertSuccessful();
    expect($provider->fresh()->enabled)->toBeFalse();
});

test('ai:provider:enable fails for an unknown slug', function () {
    $this->artisan('ai:provider:enable', ['slug' => 'nope'])->assertFailed();
});

test('ai:provider:remove --force deletes the provider and its models', function () {
    $provider = AiProvider::factory()->create(['slug' => 'openai']);
    AiModel::factory()->for($provider, 'provider')->count(2)->create();

    $this->artisan('ai:provider:remove', ['slug' => 'openai', '--force' => true])->assertSuccessful();

    expect(AiProvider::count())->toBe(0)
        ->and(AiModel::count())->toBe(0);
});

test('ai:provider:test reports a valid key', function () {
    Http::fake(['https://openrouter.ai/api/v1/models' => Http::response(['data' => [['id' => 'x/y']]])]);
    AiProvider::factory()->create(['slug' => 'or', 'name' => 'OpenRouter', 'driver' => 'openrouter', 'base_url' => null]);

    $this->artisan('ai:provider:test', ['slug' => 'or'])
        ->assertSuccessful()
        ->expectsOutputToContain('key accepted');
});

test('ai:provider:test fails when the key is rejected', function () {
    Http::fake(['*' => Http::response([], 401)]);
    AiProvider::factory()->create(['slug' => 'or', 'driver' => 'openrouter', 'base_url' => null]);

    $this->artisan('ai:provider:test', ['slug' => 'or'])->assertFailed();
});
