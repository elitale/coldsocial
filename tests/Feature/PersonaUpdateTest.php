<?php

use App\Models\Persona;
use App\Models\User;

test('a user can save their persona', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch(route('onboarding.update'), [
        'primary_goal' => 'entrepreneur',
        'headline' => 'Founder at Acme',
        'interests' => ['ai', 'startups'],
        'tones' => ['professional', 'friendly'],
        'social_links' => ['linkedin' => 'https://www.linkedin.com/in/jane'],
    ]);

    $response->assertRedirect(route('dashboard'));

    $persona = $user->fresh()->persona;

    expect($persona)->not->toBeNull();
    expect($persona->primary_goal)->toBe('entrepreneur');
    expect($persona->interests)->toBe(['ai', 'startups']);
    expect($persona->social_links)->toBe(['linkedin' => 'https://www.linkedin.com/in/jane']);
    expect($persona->completed_at)->not->toBeNull();
});

test('saving the persona updates the same record', function () {
    $user = User::factory()->create();
    Persona::factory()->for($user)->create(['primary_goal' => 'creator']);

    $this->actingAs($user)
        ->patch(route('onboarding.update'), ['primary_goal' => 'executive'])
        ->assertRedirect(route('dashboard'));

    expect(Persona::where('user_id', $user->id)->count())->toBe(1);
    expect($user->fresh()->persona->primary_goal)->toBe('executive');
});

test('empty social links are discarded', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patch(route('onboarding.update'), [
        'social_links' => ['linkedin' => '', 'x' => 'https://x.com/jane'],
    ])->assertSessionHasNoErrors();

    expect($user->fresh()->persona->social_links)->toBe(['x' => 'https://x.com/jane']);
});

test('invalid social urls are rejected', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('onboarding.edit'))
        ->patch(route('onboarding.update'), ['social_links' => ['linkedin' => 'not-a-url']])
        ->assertSessionHasErrors('social_links.linkedin');
});

test('out-of-range enum values are rejected', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('onboarding.edit'))
        ->patch(route('onboarding.update'), ['primary_goal' => 'not-a-goal'])
        ->assertSessionHasErrors('primary_goal');
});

test('invalid multi-select values are rejected', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('onboarding.edit'))
        ->patch(route('onboarding.update'), ['interests' => ['ai', 'not-an-interest']])
        ->assertSessionHasErrors('interests.1');
});
