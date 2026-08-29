<?php

use App\Enums\AiCapability;
use App\Enums\PostStatus;
use App\Models\AiModel;
use App\Models\AiProvider;
use App\Models\Persona;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;

test('guests are redirected to login', function () {
    $this->get(route('studio.create'))->assertRedirect(route('login'));
});

test('the studio renders the composer with the LinkedIn spec', function () {
    $user = User::factory()->has(Persona::factory())->create();

    $this->actingAs($user)
        ->get(route('studio.create'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('studio/index')
            ->where('platform', 'linkedin')
            ->where('spec.charLimit', 3000)
            ->where('spec.hashtagMax', 5)
        );
});

test('generate returns a caption from the persona voice', function () {
    $provider = AiProvider::factory()->create(['driver' => 'openai']);
    AiModel::factory()->for($provider, 'provider')->capability(AiCapability::Text)->default()->create();
    Http::fake(['https://api.openai.com/v1/chat/completions' => Http::response([
        'choices' => [['message' => ['content' => 'Shipped it! #startup #build #founder']]],
    ])]);

    $user = User::factory()->has(Persona::factory())->create();

    $this->actingAs($user)
        ->from(route('studio.create'))
        ->post(route('studio.generate'), ['prompt' => 'our launch'])
        ->assertRedirect(route('studio.create'))
        ->assertSessionHas('generated', 'Shipped it! #startup #build #founder');
});

test('generate fails gracefully when no text model is configured', function () {
    $user = User::factory()->has(Persona::factory())->create();

    $this->actingAs($user)
        ->from(route('studio.create'))
        ->post(route('studio.generate'), ['prompt' => 'anything'])
        ->assertSessionHasErrors('prompt');

    expect(Post::count())->toBe(0);
});

test('a body is required to save', function () {
    $user = User::factory()->has(Persona::factory())->create();

    $this->actingAs($user)
        ->from(route('studio.create'))
        ->post(route('studio.store'), [])
        ->assertSessionHasErrors('body');
});

test('saving without a time creates a LinkedIn draft', function () {
    $user = User::factory()->has(Persona::factory())->create();

    $response = $this->actingAs($user)
        ->post(route('studio.store'), ['body' => 'My first post #hello']);

    $post = Post::sole();

    $response->assertRedirect(route('posts.show', $post));

    expect($post->user_id)->toBe($user->id)
        ->and($post->platform)->toBe('linkedin')
        ->and($post->status)->toBe(PostStatus::Draft)
        ->and($post->body)->toBe('My first post #hello');
});

test('saving with a time schedules the post in the user timezone', function () {
    $user = User::factory()->has(Persona::factory())->create(['timezone' => 'Asia/Kolkata']);

    $this->actingAs($user)
        ->post(route('studio.store'), ['body' => 'Launch day #ship', 'scheduled_at' => '2099-01-01T09:00']);

    $post = Post::sole();

    // 09:00 Asia/Kolkata (UTC+5:30) == 03:30 UTC.
    expect($post->status)->toBe(PostStatus::Scheduled)
        ->and($post->scheduled_at->utc()->format('Y-m-d H:i'))->toBe('2099-01-01 03:30');
});

test('a past schedule time is rejected and nothing is saved', function () {
    $user = User::factory()->has(Persona::factory())->create(['timezone' => 'UTC']);

    $this->actingAs($user)
        ->from(route('studio.create'))
        ->post(route('studio.store'), ['body' => 'Old news #past', 'scheduled_at' => '2000-01-01T09:00'])
        ->assertSessionHasErrors('scheduled_at');

    expect(Post::count())->toBe(0);
});
