<?php

use App\Models\Persona;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('unverified users are redirected to the email verification notice', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('verification.notice'));
});

test('verified users can visit the dashboard', function () {
    $this->withoutVite();

    $user = User::factory()->create();
    Persona::factory()->for($user)->create(); // completed persona clears the onboarding gate

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
});
