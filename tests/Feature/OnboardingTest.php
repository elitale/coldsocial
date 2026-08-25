<?php

use App\Models\Persona;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('the onboarding wizard is displayed to a verified user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('onboarding.edit'))->assertOk();
});

test('guests are redirected to login from onboarding', function () {
    $this->get(route('onboarding.edit'))->assertRedirect(route('login'));
});

test('unverified users are redirected to the verification notice', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->get(route('onboarding.edit'))->assertRedirect(route('verification.notice'));
});

test('a saved persona pre-fills the wizard', function () {
    $user = User::factory()->create();
    Persona::factory()->for($user)->create(['primary_goal' => 'creator']);

    $this->actingAs($user)
        ->get(route('onboarding.edit'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('onboarding')
            ->where('persona.primary_goal', 'creator')
            ->has('options.primary_goal')
        );
});
