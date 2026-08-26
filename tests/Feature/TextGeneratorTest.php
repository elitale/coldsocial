<?php

use App\Ai\ProviderRequestException;
use App\Ai\TextGenerator;
use App\Enums\AiCapability;
use App\Models\AiModel;
use App\Models\AiProvider;
use Illuminate\Support\Facades\Http;

function chatReply(string $text): array
{
    return ['choices' => [['message' => ['content' => $text]]]];
}

test('it generates text with the default text model', function () {
    $provider = AiProvider::factory()->create(['driver' => 'openai']);
    AiModel::factory()->for($provider, 'provider')->capability(AiCapability::Text)->default()->create(['identifier' => 'gpt-4o']);

    Http::fake(['https://api.openai.com/v1/chat/completions' => Http::response(chatReply('Generated post.'))]);

    expect(app(TextGenerator::class)->generate('Write a LinkedIn post.'))->toBe('Generated post.');
});

test('it falls back to the next enabled text model when the default provider fails', function () {
    $primary = AiProvider::factory()->create(['driver' => 'openai']);
    $backup = AiProvider::factory()->create(['driver' => 'openrouter']);

    AiModel::factory()->for($primary, 'provider')->capability(AiCapability::Text)->default()->create(['identifier' => 'gpt-4o']);
    AiModel::factory()->for($backup, 'provider')->capability(AiCapability::Text)->create(['identifier' => 'meta/llama']);

    Http::fake([
        'https://api.openai.com/v1/chat/completions' => Http::response('upstream error', 500),
        'https://openrouter.ai/api/v1/chat/completions' => Http::response(chatReply('From the backup model.')),
    ]);

    expect(app(TextGenerator::class)->generate('Write a LinkedIn post.'))->toBe('From the backup model.');
});

test('it throws when every configured text model fails', function () {
    $provider = AiProvider::factory()->create(['driver' => 'openai']);
    AiModel::factory()->for($provider, 'provider')->capability(AiCapability::Text)->default()->create();

    Http::fake(['*' => Http::response('upstream error', 500)]);

    expect(fn () => app(TextGenerator::class)->generate('Write a post.'))
        ->toThrow(ProviderRequestException::class);
});

test('it throws when no enabled text model is configured', function () {
    // A text model behind a disabled provider, and an enabled non-text model — neither qualifies.
    $disabled = AiProvider::factory()->create(['enabled' => false]);
    AiModel::factory()->for($disabled, 'provider')->capability(AiCapability::Text)->default()->create();

    $enabled = AiProvider::factory()->create(['driver' => 'openai']);
    AiModel::factory()->for($enabled, 'provider')->capability(AiCapability::Image)->create();

    Http::fake();

    expect(fn () => app(TextGenerator::class)->generate('Write a post.'))
        ->toThrow(ProviderRequestException::class);

    Http::assertNothingSent();
});
