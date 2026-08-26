<?php

use App\Models\Persona;
use App\Models\User;

test('a user without a persona is sent to onboarding', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard'))
        ->assertRedirect(route('onboarding.edit'));
});

test('a user with an incomplete persona is sent to onboarding', function () {
    $user = User::factory()->create();
    Persona::factory()->for($user)->create(['completed_at' => null]);

    $this->actingAs($user)->get(route('dashboard'))
        ->assertRedirect(route('onboarding.edit'));
});

test('a user with a completed persona can reach the dashboard', function () {
    $this->withoutVite();

    $user = User::factory()->create();
    Persona::factory()->for($user)->create(); // completed_at defaults to now()

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
});

test('the onboarding page itself is not gated', function () {
    $this->withoutVite();

    $user = User::factory()->create();

    $this->actingAs($user)->get(route('onboarding.edit'))->assertOk();
});

test('guests are sent to login, not onboarding', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});
