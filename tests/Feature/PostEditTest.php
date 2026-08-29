<?php

use App\Models\Persona;
use App\Models\Post;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('guests cannot edit a draft', function () {
    $post = Post::factory()->for(User::factory())->create();

    $this->get(route('posts.edit', $post))->assertRedirect(route('login'));
});

test('a user sees the edit form pre-filled with their draft', function () {
    $user = User::factory()->has(Persona::factory())->create();
    $post = Post::factory()->for($user)->create(['body' => 'Original body']);

    $this->actingAs($user)->get(route('posts.edit', $post))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('posts/edit')
            ->where('post.body', 'Original body')
        );
});

test('a user can update their draft body', function () {
    $user = User::factory()->has(Persona::factory())->create();
    $post = Post::factory()->for($user)->create(['body' => 'Original body']);

    $this->actingAs($user)
        ->patch(route('posts.update', $post), ['body' => 'Edited body'])
        ->assertRedirect(route('posts.show', $post));

    expect($post->fresh()->body)->toBe('Edited body');
});

test('the body is required when updating', function () {
    $user = User::factory()->has(Persona::factory())->create();
    $post = Post::factory()->for($user)->create(['body' => 'Original body']);

    $this->actingAs($user)
        ->from(route('posts.edit', $post))
        ->patch(route('posts.update', $post), ['body' => ''])
        ->assertSessionHasErrors('body');

    expect($post->fresh()->body)->toBe('Original body');
});

test('a user cannot edit or update another user\'s draft', function () {
    $user = User::factory()->has(Persona::factory())->create();
    $post = Post::factory()->for(User::factory())->create(['body' => 'Not yours']);

    $this->actingAs($user)->get(route('posts.edit', $post))->assertForbidden();
    $this->actingAs($user)->patch(route('posts.update', $post), ['body' => 'Hacked'])->assertForbidden();

    expect($post->fresh()->body)->toBe('Not yours');
});
