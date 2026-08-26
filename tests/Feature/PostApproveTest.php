<?php

use App\Enums\PostStatus;
use App\Models\Persona;
use App\Models\Post;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('guests cannot approve a draft', function () {
    $post = Post::factory()->for(User::factory())->create();

    $this->post(route('posts.approve', $post))->assertRedirect(route('login'));
});

test('a new draft starts unapproved', function () {
    $post = Post::factory()->for(User::factory())->create();

    expect($post->fresh()->status)->toBe(PostStatus::Draft);
});

test('a user can approve their draft', function () {
    $user = User::factory()->has(Persona::factory())->create();
    $post = Post::factory()->for($user)->create();

    $this->actingAs($user)->post(route('posts.approve', $post))
        ->assertRedirect(route('posts.show', $post));

    expect($post->fresh()->status)->toBe(PostStatus::Approved);
});

test('a user can send an approved post back to draft', function () {
    $user = User::factory()->has(Persona::factory())->create();
    $post = Post::factory()->for($user)->approved()->create();

    $this->actingAs($user)->post(route('posts.unapprove', $post))
        ->assertRedirect(route('posts.show', $post));

    expect($post->fresh()->status)->toBe(PostStatus::Draft);
});

test('a user cannot approve another user\'s draft', function () {
    $user = User::factory()->has(Persona::factory())->create();
    $post = Post::factory()->for(User::factory())->create();

    $this->actingAs($user)->post(route('posts.approve', $post))->assertForbidden();

    expect($post->fresh()->status)->toBe(PostStatus::Draft);
});

test('the library exposes each post status', function () {
    $user = User::factory()->has(Persona::factory())->create();
    Post::factory()->for($user)->approved()->create();

    $this->actingAs($user)->get(route('posts.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('posts/index')
            ->where('posts.0.status', 'approved')
        );
});
