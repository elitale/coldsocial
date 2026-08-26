<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('guests cannot view posting settings', function () {
    $this->get(route('posting.edit'))->assertRedirect(route('login'));
});

test('a user sees their timezone on the posting settings page', function () {
    $this->withoutVite();

    $user = User::factory()->create(['timezone' => 'America/New_York']);

    $this->actingAs($user)->get(route('posting.edit'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('settings/posting')
            ->where('timezone', 'America/New_York')
            ->has('timezones')
        );
});

test('a user can set their posting timezone', function () {
    $user = User::factory()->create(['timezone' => null]);

    $this->actingAs($user)
        ->patch(route('posting.update'), ['timezone' => 'Europe/London'])
        ->assertRedirect(route('posting.edit'));

    expect($user->fresh()->timezone)->toBe('Europe/London');
});

test('an invalid timezone is rejected', function () {
    $user = User::factory()->create(['timezone' => 'UTC']);

    $this->actingAs($user)
        ->from(route('posting.edit'))
        ->patch(route('posting.update'), ['timezone' => 'Not/AZone'])
        ->assertSessionHasErrors('timezone');

    expect($user->fresh()->timezone)->toBe('UTC');
});

test('a browser alias timezone (e.g. Asia/Calcutta) is accepted', function () {
    $user = User::factory()->create(['timezone' => 'UTC']);

    $this->actingAs($user)
        ->patch(route('posting.update'), ['timezone' => 'Asia/Calcutta'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('posting.edit'));

    expect($user->fresh()->timezone)->toBe('Asia/Calcutta');
});
