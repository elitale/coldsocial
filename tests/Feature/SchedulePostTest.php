<?php

use App\Enums\PostStatus;
use App\Models\Persona;
use App\Models\Post;
use App\Models\User;

test('guests cannot schedule a post', function () {
    $post = Post::factory()->for(User::factory())->approved()->create();

    $this->post(route('posts.schedule', $post), ['scheduled_at' => '2099-01-01T09:00'])
        ->assertRedirect(route('login'));
});

test('a user schedules an approved post', function () {
    $user = User::factory()->has(Persona::factory())->create(['timezone' => 'UTC']);
    $post = Post::factory()->for($user)->approved()->create();

    $this->actingAs($user)
        ->post(route('posts.schedule', $post), ['scheduled_at' => '2099-01-01T09:00'])
        ->assertRedirect(route('posts.show', $post));

    $post->refresh();
    expect($post->status)->toBe(PostStatus::Scheduled)
        ->and($post->scheduled_at->utc()->format('Y-m-d H:i'))->toBe('2099-01-01 09:00');
});

test('the scheduled time is interpreted in the user timezone', function () {
    $user = User::factory()->has(Persona::factory())->create(['timezone' => 'Asia/Kolkata']);
    $post = Post::factory()->for($user)->approved()->create();

    $this->actingAs($user)
        ->post(route('posts.schedule', $post), ['scheduled_at' => '2099-01-01T09:00']);

    // 09:00 Asia/Kolkata (UTC+5:30) == 03:30 UTC.
    expect($post->fresh()->scheduled_at->utc()->format('Y-m-d H:i'))->toBe('2099-01-01 03:30');
});

test('a draft cannot be scheduled (must be approved first)', function () {
    $user = User::factory()->has(Persona::factory())->create();
    $post = Post::factory()->for($user)->create();

    $this->actingAs($user)
        ->from(route('posts.show', $post))
        ->post(route('posts.schedule', $post), ['scheduled_at' => '2099-01-01T09:00'])
        ->assertSessionHasErrors('scheduled_at');

    expect($post->fresh()->scheduled_at)->toBeNull();
});

test('a post cannot be scheduled in the past', function () {
    $user = User::factory()->has(Persona::factory())->create(['timezone' => 'UTC']);
    $post = Post::factory()->for($user)->approved()->create();

    $this->actingAs($user)
        ->from(route('posts.show', $post))
        ->post(route('posts.schedule', $post), ['scheduled_at' => '2000-01-01T09:00'])
        ->assertSessionHasErrors('scheduled_at');

    expect($post->fresh()->status)->toBe(PostStatus::Approved);
});

test('a user can unschedule a scheduled post', function () {
    $user = User::factory()->has(Persona::factory())->create();
    $post = Post::factory()->for($user)->scheduled()->create();

    $this->actingAs($user)
        ->post(route('posts.unschedule', $post))
        ->assertRedirect(route('posts.show', $post));

    $post->refresh();
    expect($post->status)->toBe(PostStatus::Approved)
        ->and($post->scheduled_at)->toBeNull();
});

test('a user cannot schedule another user\'s post', function () {
    $user = User::factory()->has(Persona::factory())->create();
    $post = Post::factory()->for(User::factory())->approved()->create();

    $this->actingAs($user)
        ->post(route('posts.schedule', $post), ['scheduled_at' => '2099-01-01T09:00'])
        ->assertForbidden();

    expect($post->fresh()->scheduled_at)->toBeNull();
});
