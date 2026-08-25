<?php

use App\Models\User;

test('the root route redirects guests to the login page', function () {
    $this->get(route('home'))->assertRedirect(route('login'));
});

test('the root route redirects authenticated users to the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('home'))->assertRedirect(route('dashboard'));
});
