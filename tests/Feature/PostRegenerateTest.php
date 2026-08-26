<?php

use App\Enums\AiCapability;
use App\Models\AiModel;
use App\Models\AiProvider;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;

function seedTextModelForRewrite(): void
{
    $provider = AiProvider::factory()->create(['driver' => 'openai']);
    AiModel::factory()->for($provider, 'provider')->capability(AiCapability::Text)->default()->create();
}

test('guests cannot regenerate a draft', function () {
    $post = Post::factory()->for(User::factory())->create();

    $this->post(route('posts.regenerate', $post), ['instruction' => 'Make it shorter'])
        ->assertRedirect(route('login'));
});

test('a user regenerates their draft from an instruction', function () {
    seedTextModelForRewrite();
    Http::fake(['https://api.openai.com/v1/chat/completions' => Http::response([
        'choices' => [['message' => ['content' => 'Shorter revised post.']]],
    ])]);

    $user = User::factory()->create();
    $post = Post::factory()->for($user)->create(['body' => 'Original long post body.']);

    $this->actingAs($user)
        ->post(route('posts.regenerate', $post), ['instruction' => 'Make it shorter'])
        ->assertRedirect(route('posts.show', $post));

    expect($post->fresh()->body)->toBe('Shorter revised post.');
});

test('the instruction and current body are sent to the model', function () {
    seedTextModelForRewrite();
    Http::fake(['https://api.openai.com/v1/chat/completions' => Http::response([
        'choices' => [['message' => ['content' => 'Revised.']]],
    ])]);

    $user = User::factory()->create();
    $post = Post::factory()->for($user)->create(['body' => 'BODY-SEED']);

    $this->actingAs($user)->post(route('posts.regenerate', $post), ['instruction' => 'INSTRUCTION-SEED']);

    Http::assertSent(fn (ClientRequest $request): bool => str_contains($request->body(), 'INSTRUCTION-SEED')
        && str_contains($request->body(), 'BODY-SEED'));
});

test('the instruction is required', function () {
    seedTextModelForRewrite();
    Http::fake();

    $user = User::factory()->create();
    $post = Post::factory()->for($user)->create(['body' => 'Original']);

    $this->actingAs($user)
        ->from(route('posts.show', $post))
        ->post(route('posts.regenerate', $post), ['instruction' => ''])
        ->assertSessionHasErrors('instruction');

    expect($post->fresh()->body)->toBe('Original');
    Http::assertNothingSent();
});

test('regenerate fails gracefully when no text model is configured', function () {
    Http::fake();

    $user = User::factory()->create();
    $post = Post::factory()->for($user)->create(['body' => 'Original']);

    $this->actingAs($user)
        ->from(route('posts.show', $post))
        ->post(route('posts.regenerate', $post), ['instruction' => 'Make it punchy'])
        ->assertRedirect(route('posts.show', $post))
        ->assertSessionHasErrors('regenerate');

    expect($post->fresh()->body)->toBe('Original');
});

test('a user cannot regenerate another user\'s draft', function () {
    seedTextModelForRewrite();
    Http::fake();

    $user = User::factory()->create();
    $post = Post::factory()->for(User::factory())->create(['body' => 'Not yours']);

    $this->actingAs($user)
        ->post(route('posts.regenerate', $post), ['instruction' => 'Change it'])
        ->assertForbidden();

    expect($post->fresh()->body)->toBe('Not yours');
    Http::assertNothingSent();
});
