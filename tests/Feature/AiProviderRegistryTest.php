<?php

use App\Enums\AiCapability;
use App\Models\AiModel;
use App\Models\AiProvider;
use Illuminate\Support\Facades\DB;

test('a provider stores its api key encrypted and hidden', function () {
    $provider = AiProvider::factory()->create(['api_key' => 'secret-key-123']);

    // Round-trips through the model.
    expect($provider->fresh()->api_key)->toBe('secret-key-123');

    // The raw column is not the plaintext.
    $raw = DB::table('ai_providers')->where('id', $provider->id)->value('api_key');
    expect($raw)->not->toBe('secret-key-123');

    // Never serialized to arrays/JSON.
    expect($provider->toArray())->not->toHaveKey('api_key');
});

test('a model casts its capability and belongs to a provider', function () {
    $provider = AiProvider::factory()->create();
    $model = AiModel::factory()->for($provider, 'provider')->capability(AiCapability::Thinking)->create();

    expect($model->capability)->toBe(AiCapability::Thinking)
        ->and($model->provider->is($provider))->toBeTrue()
        ->and($provider->models)->toHaveCount(1);
});

test('deleting a provider cascades to its models', function () {
    $provider = AiProvider::factory()->create();
    AiModel::factory()->for($provider, 'provider')->count(2)->create();

    $provider->delete();

    expect(AiModel::count())->toBe(0);
});

test('setting a new default clears the previous default for the same capability', function () {
    $first = AiModel::factory()->capability(AiCapability::Text)->default()->create();
    $second = AiModel::factory()->capability(AiCapability::Text)->default()->create();

    expect($first->fresh()->is_default)->toBeFalse()
        ->and($second->fresh()->is_default)->toBeTrue()
        ->and(AiModel::where('capability', AiCapability::Text)->where('is_default', true)->count())->toBe(1);
});

test('defaults for different capabilities coexist', function () {
    $text = AiModel::factory()->capability(AiCapability::Text)->default()->create();
    $image = AiModel::factory()->capability(AiCapability::Image)->default()->create();

    expect($text->fresh()->is_default)->toBeTrue()
        ->and($image->fresh()->is_default)->toBeTrue();
});
