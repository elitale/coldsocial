<?php

use App\Ai\ModelTester;
use App\Ai\OpenAiCompatibleChat;
use App\Ai\ProviderRequestException;
use App\Enums\AiCapability;
use App\Models\AiModel;
use App\Models\AiProvider;
use Illuminate\Support\Facades\Http;

function openrouterModel(AiCapability $capability = AiCapability::Text, string $identifier = 'x/y'): AiModel
{
    $provider = AiProvider::factory()->create([
        'slug' => 'or-'.fake()->unique()->numerify('####'),
        'name' => 'OpenRouter',
        'driver' => 'openrouter',
        'base_url' => null,
    ]);

    return AiModel::factory()->for($provider, 'provider')->capability($capability)->create(['identifier' => $identifier]);
}

test('the chat client returns the assistant reply', function () {
    Http::fake(['https://openrouter.ai/api/v1/chat/completions' => Http::response([
        'choices' => [['message' => ['content' => 'Hello there!']]],
    ])]);

    $reply = app(OpenAiCompatibleChat::class)->complete(openrouterModel(), 'hi');

    expect($reply)->toBe('Hello there!');
});

test('the chat client throws when the key is rejected', function () {
    Http::fake(['*' => Http::response([], 401)]);

    app(OpenAiCompatibleChat::class)->complete(openrouterModel(), 'hi');
})->throws(ProviderRequestException::class);

test('the chat client throws on an empty response', function () {
    Http::fake(['*' => Http::response(['choices' => [['message' => ['content' => '']]]])]);

    app(OpenAiCompatibleChat::class)->complete(openrouterModel(), 'hi');
})->throws(ProviderRequestException::class);

test('the tester supports text and thinking but not image', function () {
    $tester = app(ModelTester::class);

    expect($tester->supports(openrouterModel(AiCapability::Text, 't')))->toBeTrue()
        ->and($tester->supports(openrouterModel(AiCapability::Thinking, 'th')))->toBeTrue()
        ->and($tester->supports(openrouterModel(AiCapability::Image, 'i')))->toBeFalse();
});

test('ai:model:test reports the model reply', function () {
    Http::fake(['https://openrouter.ai/api/v1/chat/completions' => Http::response([
        'choices' => [['message' => ['content' => 'pong']]],
    ])]);
    openrouterModel();

    $this->artisan('ai:model:test', ['identifier' => 'x/y'])
        ->assertSuccessful()
        ->expectsOutputToContain('Model responded');
});

test('ai:model:test warns for a not-yet-supported capability', function () {
    openrouterModel(AiCapability::Image, 'img');

    $this->artisan('ai:model:test', ['identifier' => 'img'])
        ->assertSuccessful()
        ->expectsOutputToContain("isn't wired up yet");
});

test('ai:model:test fails for an unknown model', function () {
    $this->artisan('ai:model:test', ['identifier' => 'nope'])->assertFailed();
});
