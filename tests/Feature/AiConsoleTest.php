<?php

use App\Enums\AiCapability;
use App\Models\AiModel;
use App\Models\AiProvider;
use Illuminate\Support\Facades\Http;

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
function aiDriverOptions(): array
{
    return [
        'openai' => 'OpenAI (OpenAI-compatible)',
        'openrouter' => 'OpenRouter',
        'github' => 'GitHub Models / Copilot',
        'anthropic' => 'Anthropic',
        'gemini' => 'Google Gemini',
        'other' => 'Other…',
    ];
}

test('the ai console exits cleanly', function () {
    $this->artisan('ai')
        ->expectsChoice('What would you like to do?', 'exit', aiConsoleMenu())
        ->assertSuccessful();
});

test('the ai console adds a provider through the menu', function () {
    $this->artisan('ai')
        ->expectsChoice('What would you like to do?', 'provider:add', aiConsoleMenu())
        ->expectsQuestion('Provider name', 'OpenAI')
        ->expectsChoice('Driver', 'openai', aiDriverOptions())
        ->expectsQuestion('API key (optional)', 'sk-secret')
        ->expectsQuestion('Base URL (optional)', '')
        ->expectsChoice('What would you like to do?', 'exit', aiConsoleMenu())
        ->assertSuccessful();

    $provider = AiProvider::where('slug', 'openai')->sole();
    expect($provider->driver)->toBe('openai')
        ->and($provider->api_key)->toBe('sk-secret');
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
        ])
        ->expectsChoice('What would you like to do?', 'exit', aiConsoleMenu())
        ->assertSuccessful();

    expect($new->fresh()->is_default)->toBeTrue()
        ->and($old->fresh()->is_default)->toBeFalse();
});

test('the ai console lists the provider models when adding a model', function () {
    Http::fake(['https://openrouter.ai/api/v1/models' => Http::response([
        'data' => [['id' => 'openai/gpt-4o'], ['id' => 'x/y']],
    ])]);

    AiProvider::factory()->create(['slug' => 'or', 'name' => 'OpenRouter', 'driver' => 'openrouter', 'base_url' => null]);

    $this->artisan('ai')
        ->expectsChoice('What would you like to do?', 'model:add', aiConsoleMenu())
        ->expectsChoice('Provider', 'or', ['or' => 'OpenRouter'])
        ->expectsChoice('Capability', 'text', capabilityChoices())
        ->expectsChoice('Model', 'openai/gpt-4o', [
            'openai/gpt-4o' => 'openai/gpt-4o',
            'x/y' => 'x/y',
            '__manual__' => 'Enter manually…',
        ])
        ->expectsConfirmation('Make this the default text model?', 'no')
        ->expectsChoice('What would you like to do?', 'exit', aiConsoleMenu())
        ->assertSuccessful();

    expect(AiModel::where('identifier', 'openai/gpt-4o')->exists())->toBeTrue();
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
        ])
        ->expectsChoice('What would you like to do?', 'exit', aiConsoleMenu())
        ->assertSuccessful();
});

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
