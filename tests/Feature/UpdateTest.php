<?php

use App\Models\Persona;
use App\Models\Update;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('guests are redirected from the updates page', function () {
    $this->get(route('updates.index'))->assertRedirect(route('login'));
});

test('a user can capture an update', function () {
    $user = User::factory()->has(Persona::factory())->create();

    $this->actingAs($user)
        ->post(route('updates.store'), [
            'body' => 'Shipped the new onboarding flow today.',
            'source_url' => 'https://example.com/launch',
        ])
        ->assertRedirect(route('updates.index'));

    $update = $user->updates()->sole();

    expect($update->body)->toBe('Shipped the new onboarding flow today.')
        ->and($update->source_url)->toBe('https://example.com/launch');
});

test('the body is required', function () {
    $user = User::factory()->has(Persona::factory())->create();

    $this->actingAs($user)
        ->from(route('updates.index'))
        ->post(route('updates.store'), ['body' => ''])
        ->assertSessionHasErrors('body');

    expect($user->updates()->count())->toBe(0);
});

test('an invalid source url is rejected', function () {
    $user = User::factory()->has(Persona::factory())->create();

    $this->actingAs($user)
        ->from(route('updates.index'))
        ->post(route('updates.store'), ['body' => 'A valid body', 'source_url' => 'not-a-url'])
        ->assertSessionHasErrors('source_url');
});

test('the index lists only the current user\'s updates', function () {
    $user = User::factory()->has(Persona::factory())->create();
    Update::factory()->for($user)->create(['body' => 'Mine']);
    Update::factory()->for(User::factory())->create(['body' => 'Theirs']);

    $this->actingAs($user)
        ->get(route('updates.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('updates/index')
            ->has('updates', 1)
            ->where('updates.0.body', 'Mine')
        );
});

test('a user can delete their own update', function () {
    $user = User::factory()->has(Persona::factory())->create();
    $update = Update::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('updates.destroy', $update))
        ->assertRedirect(route('updates.index'));

    expect(Update::whereKey($update->id)->exists())->toBeFalse();
});

test('a user cannot delete another user\'s update', function () {
    $user = User::factory()->has(Persona::factory())->create();
    $update = Update::factory()->for(User::factory())->create();

    $this->actingAs($user)
        ->delete(route('updates.destroy', $update))
        ->assertForbidden();

    expect(Update::whereKey($update->id)->exists())->toBeTrue();
});
