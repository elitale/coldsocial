<?php

use App\Ai\ModelCatalog;
use App\Ai\OpenAiCompatibleCatalog;
use App\Ai\ProviderRequestException;
use App\Models\AiProvider;
use Illuminate\Support\Facades\Http;

test('the catalog lists models from an OpenAI-compatible provider', function () {
    Http::fake([
        'https://openrouter.ai/api/v1/models' => Http::response([
            'data' => [
                ['id' => 'openai/gpt-4o'],
                ['id' => 'anthropic/claude-3.7-sonnet'],
            ],
        ]),
    ]);

    $provider = AiProvider::factory()->create([
        'driver' => 'openrouter',
        'base_url' => null,
        'api_key' => 'sk-x',
    ]);

    $models = app(OpenAiCompatibleCatalog::class)->models($provider);

    expect($models)->toBe(['openai/gpt-4o', 'anthropic/claude-3.7-sonnet']);
});

test('the catalog throws when the provider rejects the key', function () {
    Http::fake(['*' => Http::response(['error' => 'unauthorized'], 401)]);

    $provider = AiProvider::factory()->create([
        'driver' => 'openai',
        'base_url' => null,
        'api_key' => 'bad-key',
    ]);

    app(OpenAiCompatibleCatalog::class)->models($provider);
})->throws(ProviderRequestException::class);

test('the catalog only supports providers it can reach', function () {
    $anthropic = AiProvider::factory()->create(['driver' => 'anthropic', 'base_url' => null]);
    $custom = AiProvider::factory()->create(['driver' => 'custom', 'base_url' => 'https://example.test/v1']);

    $catalog = app(ModelCatalog::class);

    expect($catalog->supports($anthropic))->toBeFalse()
        ->and($catalog->supports($custom))->toBeTrue();
});
