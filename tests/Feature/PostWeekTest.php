<?php

use App\Enums\AiCapability;
use App\Models\AiModel;
use App\Models\AiProvider;
use App\Models\Update;
use App\Models\User;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;

function seedTextModelForWeek(): void
{
    $provider = AiProvider::factory()->create(['driver' => 'openai']);
    AiModel::factory()->for($provider, 'provider')->capability(AiCapability::Text)->default()->create();
}

test('guests cannot generate a week', function () {
    $this->post(route('posts.week'))->assertRedirect(route('login'));
});

test('a user generates five linkedin drafts', function () {
    seedTextModelForWeek();
    Http::fake(['https://api.openai.com/v1/chat/completions' => Http::response([
        'choices' => [['message' => ['content' => 'A generated post.']]],
    ])]);

    $user = User::factory()->create();

    $this->actingAs($user)->post(route('posts.week'))->assertRedirect(route('posts.index'));

    expect($user->posts()->count())->toBe(5)
        ->and($user->posts()->pluck('platform')->unique()->values()->all())->toBe(['linkedin']);
});

test('the week draws on recent updates', function () {
    seedTextModelForWeek();
    Http::fake(['https://api.openai.com/v1/chat/completions' => Http::response([
        'choices' => [['message' => ['content' => 'Post']]],
    ])]);

    $user = User::factory()->create();
    Update::factory()->for($user)->create(['body' => 'WEEK-CONTEXT-SEED']);

    $this->actingAs($user)->post(route('posts.week'));

    Http::assertSent(fn (ClientRequest $request): bool => str_contains($request->body(), 'WEEK-CONTEXT-SEED'));
});

test('a week can be generated without any updates', function () {
    seedTextModelForWeek();
    Http::fake(['https://api.openai.com/v1/chat/completions' => Http::response([
        'choices' => [['message' => ['content' => 'Evergreen post']]],
    ])]);

    $user = User::factory()->create();

    $this->actingAs($user)->post(route('posts.week'))->assertRedirect(route('posts.index'));

    expect($user->posts()->count())->toBe(5);
});

test('a failed week creates nothing', function () {
    Http::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('posts.index'))
        ->post(route('posts.week'))
        ->assertRedirect(route('posts.index'))
        ->assertSessionHasErrors('generate');

    expect($user->posts()->count())->toBe(0);
});
