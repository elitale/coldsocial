<?php

use App\Models\Persona;
use App\Models\Post;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('guests cannot view the post library', function () {
    $this->get(route('posts.index'))->assertRedirect(route('login'));
});

test('the library lists only the user\'s drafts newest first', function () {
    $user = User::factory()->has(Persona::factory())->create();
    Post::factory()->for($user)->create(['body' => 'Older draft', 'created_at' => now()->subMinute()]);
    Post::factory()->for($user)->create(['body' => 'Newer draft', 'created_at' => now()]);
    Post::factory()->for(User::factory())->create(['body' => 'Someone else']);

    $this->actingAs($user)
        ->get(route('posts.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('posts/index')
            ->has('posts', 2)
            ->where('posts.0.body', 'Newer draft')
            ->where('posts.1.body', 'Older draft')
        );
});

test('a user can delete their own draft', function () {
    $user = User::factory()->has(Persona::factory())->create();
    $post = Post::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('posts.destroy', $post))
        ->assertRedirect(route('posts.index'));

    expect(Post::whereKey($post->id)->exists())->toBeFalse();
});

test('a user cannot delete another user\'s draft', function () {
    $user = User::factory()->has(Persona::factory())->create();
    $post = Post::factory()->for(User::factory())->create();

    $this->actingAs($user)
        ->delete(route('posts.destroy', $post))
        ->assertForbidden();

    expect(Post::whereKey($post->id)->exists())->toBeTrue();
});
