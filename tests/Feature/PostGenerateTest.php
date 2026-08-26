<?php

use App\Enums\AiCapability;
use App\Models\AiModel;
use App\Models\AiProvider;
use App\Models\Post;
use App\Models\Update;
use App\Models\User;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;

function seedDefaultTextModel(): void
{
    $provider = AiProvider::factory()->create(['driver' => 'openai']);
    AiModel::factory()->for($provider, 'provider')->capability(AiCapability::Text)->default()->create();
}

function fakeChatReply(string $text): void
{
    Http::fake(['https://api.openai.com/v1/chat/completions' => Http::response([
        'choices' => [['message' => ['content' => $text]]],
    ])]);
}

test('guests cannot generate a post', function () {
    $update = Update::factory()->for(User::factory())->create();

    $this->post(route('posts.store'), ['update_id' => $update->id])
        ->assertRedirect(route('login'));
});

test('a user generates a LinkedIn draft from their update', function () {
    seedDefaultTextModel();
    fakeChatReply('Big news: we shipped! 🚀');

    $user = User::factory()->create();
    $update = Update::factory()->for($user)->create(['body' => 'We launched our new API today.']);

    $response = $this->actingAs($user)->post(route('posts.store'), ['update_id' => $update->id]);

    $post = Post::query()->sole();
    $response->assertRedirect(route('posts.show', $post));

    expect($post->user_id)->toBe($user->id)
        ->and($post->update_id)->toBe($update->id)
        ->and($post->platform)->toBe('linkedin')
        ->and($post->body)->toBe('Big news: we shipped! 🚀');
});

test('generation feeds the update text into the model prompt', function () {
    seedDefaultTextModel();
    fakeChatReply('Draft');

    $user = User::factory()->create();
    $update = Update::factory()->for($user)->create(['body' => 'UNIQUE-SEED-TEXT']);

    $this->actingAs($user)->post(route('posts.store'), ['update_id' => $update->id]);

    Http::assertSent(fn (ClientRequest $request): bool => str_contains($request->body(), 'UNIQUE-SEED-TEXT'));
});

test('a user cannot generate from another user\'s update', function () {
    seedDefaultTextModel();
    Http::fake();

    $user = User::factory()->create();
    $foreign = Update::factory()->for(User::factory())->create();

    $this->actingAs($user)->post(route('posts.store'), ['update_id' => $foreign->id])
        ->assertNotFound();

    expect(Post::count())->toBe(0);
    Http::assertNothingSent();
});

test('generation fails gracefully when no text model is configured', function () {
    Http::fake();

    $user = User::factory()->create();
    $update = Update::factory()->for($user)->create();

    $this->actingAs($user)
        ->from(route('updates.index'))
        ->post(route('posts.store'), ['update_id' => $update->id])
        ->assertRedirect(route('updates.index'))
        ->assertSessionHasErrors('generate');

    expect(Post::count())->toBe(0);
});

test('a user can view their own draft', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user)->create(['body' => 'My draft body']);

    $this->actingAs($user)->get(route('posts.show', $post))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('posts/show')
            ->where('post.body', 'My draft body')
        );
});

test('a user cannot view another user\'s draft', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for(User::factory())->create();

    $this->actingAs($user)->get(route('posts.show', $post))->assertForbidden();
});
