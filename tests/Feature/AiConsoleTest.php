<?php

use App\Enums\AiCapability;
use App\Models\AiModel;
use App\Models\AiProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

/**
 * @return array<string, string>
 */
function aiConsoleMenu(): array
{
    return [
        'provider:add' => 'Add a provider',
        'model:add' => 'Add a model',
        'model:default' => 'Set the default model for a capability',
        'provider:list' => 'List providers',
        'model:list' => 'List models',
        'model:test' => 'Test a model',
        'provider:test' => 'Test a provider connection',
        'provider:toggle' => 'Enable or disable a provider',
        'provider:remove' => 'Remove a provider',
        'exit' => 'Exit',
    ];
}

/**
 * @return array<string, string>
 */
function aiPresetOptions(): array
{
    return [
        'openai' => 'OpenAI',
        'openrouter' => 'OpenRouter',
        'anthropic' => 'Anthropic',
        'gemini' => 'Google Gemini',
        'github' => 'GitHub Models',
        'copilot' => 'GitHub Copilot (device login)',
        'custom' => 'Custom (enter details manually)',
        '__cancel__' => '← Cancel',
    ];
}

/**
 * @return array<string, string>
 */
function aiDriverOptions(): array
{
    return [
        'openai' => 'OpenAI (OpenAI-compatible)',
        'openrouter' => 'OpenRouter',
        'github' => 'GitHub Models',
        'anthropic' => 'Anthropic',
        'gemini' => 'Google Gemini',
        'other' => 'Other…',
    ];
}

/**
 * @return array<string, string>
 */
function capabilityChoices(): array
{
    $choices = [];

    foreach (AiCapability::cases() as $capability) {
        $choices[$capability->value] = ucfirst($capability->value);
    }

    return $choices;
}

test('the ai console guides a first-time admin straight into adding a provider', function () {
    // No providers yet → the preset picker is the very first prompt (not the menu).
    $this->artisan('ai')
        ->expectsChoice('Which provider?', '__cancel__', aiPresetOptions())
        ->expectsChoice('What would you like to do?', 'exit', aiConsoleMenu())
        ->assertSuccessful();

    expect(AiProvider::count())->toBe(0);
});

test('the ai console shows a status line then exits', function () {
    $provider = AiProvider::factory()->create(['slug' => 'or', 'name' => 'OpenRouter']);
    AiModel::factory()->for($provider, 'provider')->capability(AiCapability::Text)->default()->create(['identifier' => 'gpt-4o']);

    $this->artisan('ai')
        ->expectsChoice('What would you like to do?', 'exit', aiConsoleMenu())
        ->expectsOutputToContain('Providers:')
        ->expectsOutputToContain('gpt-4o')
        ->assertSuccessful();
});

test('the ai console adds a provider from a preset (driver + base URL auto-filled)', function () {
    AiProvider::factory()->create(['slug' => 'seed', 'name' => 'Seed']); // avoid the first-run flow

    $this->artisan('ai')
        ->expectsChoice('What would you like to do?', 'provider:add', aiConsoleMenu())
        ->expectsChoice('Which provider?', 'openrouter', aiPresetOptions())
        ->expectsQuestion('A name for this provider', 'OpenRouter')
        ->expectsQuestion('API key', 'sk-secret')
        ->expectsConfirmation('Test the connection now?', 'no')
        ->expectsConfirmation('Add a model from this provider now?', 'no')
        ->expectsChoice('What would you like to do?', 'exit', aiConsoleMenu())
        ->assertSuccessful();

    $provider = AiProvider::where('slug', 'openrouter')->sole();
    expect($provider->driver)->toBe('openrouter')
        ->and($provider->api_key)->toBe('sk-secret')
        ->and($provider->base_url)->toBeNull();
});

test('the ai console adds a custom provider with a manual driver + base URL', function () {
    AiProvider::factory()->create(['slug' => 'seed', 'name' => 'Seed']);

    $this->artisan('ai')
        ->expectsChoice('What would you like to do?', 'provider:add', aiConsoleMenu())
        ->expectsChoice('Which provider?', 'custom', aiPresetOptions())
        ->expectsChoice('Driver', 'openai', aiDriverOptions())
        ->expectsQuestion('Base URL (optional)', 'https://my.host/v1')
        ->expectsQuestion('Provider name', 'My Host')
        ->expectsQuestion('API key', 'sk-x')
        ->expectsConfirmation('Test the connection now?', 'no')
        ->expectsConfirmation('Add a model from this provider now?', 'no')
        ->expectsChoice('What would you like to do?', 'exit', aiConsoleMenu())
        ->assertSuccessful();

    $provider = AiProvider::where('slug', 'my-host')->sole();
    expect($provider->driver)->toBe('openai')
        ->and($provider->base_url)->toBe('https://my.host/v1');
});

test('the ai console adds GitHub Copilot through the device-login preset', function () {
    Sleep::fake();
    Http::fake([
        'https://github.com/login/device/code' => Http::response([
            'device_code' => 'dev-123',
            'user_code' => 'ABCD-1234',
            'verification_uri' => 'https://github.com/login/device',
            'interval' => 1,
            'expires_in' => 900,
        ]),
        'https://github.com/login/oauth/access_token' => Http::response(['access_token' => 'gho_console_token']),
    ]);

    AiProvider::factory()->create(['slug' => 'seed', 'name' => 'Seed']); // avoid the first-run flow

    $this->artisan('ai')
        ->expectsChoice('What would you like to do?', 'provider:add', aiConsoleMenu())
        ->expectsChoice('Which provider?', 'copilot', aiPresetOptions())
        ->expectsQuestion('A name for this provider', 'GitHub Copilot')
        ->expectsConfirmation('Test the connection now?', 'no')
        ->expectsConfirmation('Add a model from this provider now?', 'no')
        ->expectsChoice('What would you like to do?', 'exit', aiConsoleMenu())
        ->assertSuccessful();

    $provider = AiProvider::where('slug', 'github-copilot')->sole();
    expect($provider->driver)->toBe('copilot')
        ->and($provider->api_key)->toBe('gho_console_token')
        ->and($provider->base_url)->toBeNull();
});

test('the ai console searches the provider models when adding a model', function () {
    Http::fake(['https://openrouter.ai/api/v1/models' => Http::response([
        'data' => [['id' => 'openai/gpt-4o'], ['id' => 'x/y']],
    ])]);

    AiProvider::factory()->create(['slug' => 'or', 'name' => 'OpenRouter', 'driver' => 'openrouter', 'base_url' => null]);

    $this->artisan('ai')
        ->expectsChoice('What would you like to do?', 'model:add', aiConsoleMenu())
        ->expectsChoice('Provider', 'or', ['or' => 'OpenRouter', '__cancel__' => '← Cancel'])
        ->expectsChoice('Capability', 'text', capabilityChoices())
        ->expectsSearch('Model (type to filter)', 'openai/gpt-4o', 'gpt', [
            'openai/gpt-4o' => 'openai/gpt-4o',
            '__manual__' => 'Enter manually…',
        ])
        ->expectsConfirmation('Make this the default text model?', 'no')
        ->expectsConfirmation('Test this model now?', 'no')
        ->expectsChoice('What would you like to do?', 'exit', aiConsoleMenu())
        ->assertSuccessful();

    expect(AiModel::where('identifier', 'openai/gpt-4o')->exists())->toBeTrue();
});

test('the ai console sets the default model through the menu', function () {
    $provider = AiProvider::factory()->create(['slug' => 'openai']);
    $old = AiModel::factory()->for($provider, 'provider')->capability(AiCapability::Text)->default()->create(['identifier' => 'gpt-4o-mini']);
    $new = AiModel::factory()->for($provider, 'provider')->capability(AiCapability::Text)->create(['identifier' => 'gpt-4o']);

    $this->artisan('ai')
        ->expectsChoice('What would you like to do?', 'model:default', aiConsoleMenu())
        ->expectsChoice('Capability', 'text', capabilityChoices())
        ->expectsChoice('Default model', (string) $new->id, [
            (string) $old->id => 'openai / gpt-4o-mini (current)',
            (string) $new->id => 'openai / gpt-4o',
            '__cancel__' => '← Cancel',
        ])
        ->expectsChoice('What would you like to do?', 'exit', aiConsoleMenu())
        ->assertSuccessful();

    expect($new->fresh()->is_default)->toBeTrue()
        ->and($old->fresh()->is_default)->toBeFalse();
});

test('the ai console tests a model through the menu', function () {
    Http::fake(['https://openrouter.ai/api/v1/chat/completions' => Http::response([
        'choices' => [['message' => ['content' => 'pong']]],
    ])]);

    $provider = AiProvider::factory()->create(['slug' => 'or', 'name' => 'OpenRouter', 'driver' => 'openrouter', 'base_url' => null]);
    $model = AiModel::factory()->for($provider, 'provider')->capability(AiCapability::Text)->create(['identifier' => 'x/y']);

    $this->artisan('ai')
        ->expectsChoice('What would you like to do?', 'model:test', aiConsoleMenu())
        ->expectsChoice('Model to test', (string) $model->id, [
            (string) $model->id => 'or / x/y (text)',
            '__cancel__' => '← Cancel',
        ])
        ->expectsChoice('What would you like to do?', 'exit', aiConsoleMenu())
        ->assertSuccessful();
});

test('cancelling the provider picker writes nothing', function () {
    AiProvider::factory()->create(['slug' => 'or', 'name' => 'OpenRouter']);

    $this->artisan('ai')
        ->expectsChoice('What would you like to do?', 'model:add', aiConsoleMenu())
        ->expectsChoice('Provider', '__cancel__', ['or' => 'OpenRouter', '__cancel__' => '← Cancel'])
        ->expectsChoice('What would you like to do?', 'exit', aiConsoleMenu())
        ->assertSuccessful();

    expect(AiModel::count())->toBe(0);
});
