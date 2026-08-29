<?php

use App\Enums\SocialPlatform;
use App\Models\Persona;
use App\Models\PlatformConnection;
use App\Models\User;
use Illuminate\Support\Facades\Http;

test('guests cannot disconnect', function () {
    $user = User::factory()->has(Persona::factory())->create();
    PlatformConnection::factory()->for($user)->create(['platform' => SocialPlatform::Linkedin]);

    $this->delete(route('connections.destroy', ['platform' => 'linkedin']))
        ->assertRedirect(route('login'));
});

test('a user disconnects a connected platform', function () {
    Http::fake(['*/revoke' => Http::response([], 200)]);

    $user = User::factory()->has(Persona::factory())->create();
    PlatformConnection::factory()->for($user)->create(['platform' => SocialPlatform::Linkedin]);

    $this->actingAs($user)
        ->delete(route('connections.destroy', ['platform' => 'linkedin']))
        ->assertRedirect(route('connections.index'))
        ->assertSessionHas('success');

    expect($user->connections()->count())->toBe(0);
});

test('disconnecting a platform that is not connected is a 404', function () {
    $user = User::factory()->has(Persona::factory())->create();

    $this->actingAs($user)
        ->delete(route('connections.destroy', ['platform' => 'linkedin']))
        ->assertNotFound();
});

test('a user cannot disconnect another user\'s connection', function () {
    Http::fake(['*/revoke' => Http::response([], 200)]);

    $user = User::factory()->has(Persona::factory())->create();
    $other = User::factory()->has(Persona::factory())->create();
    PlatformConnection::factory()->for($other)->create(['platform' => SocialPlatform::Linkedin]);

    $this->actingAs($user)
        ->delete(route('connections.destroy', ['platform' => 'linkedin']))
        ->assertNotFound();

    expect($other->connections()->count())->toBe(1);
});

test('a failed remote revoke still disconnects locally', function () {
    Http::fake(['*/revoke' => Http::response('nope', 500)]);

    $user = User::factory()->has(Persona::factory())->create();
    PlatformConnection::factory()->for($user)->create(['platform' => SocialPlatform::Linkedin]);

    $this->actingAs($user)
        ->delete(route('connections.destroy', ['platform' => 'linkedin']))
        ->assertRedirect(route('connections.index'));

    expect($user->connections()->count())->toBe(0);
});

test('disconnecting one platform leaves others untouched', function () {
    Http::fake(['*/revoke' => Http::response([], 200)]);

    $user = User::factory()->has(Persona::factory())->create();
    PlatformConnection::factory()->for($user)->create(['platform' => SocialPlatform::Linkedin]);
    PlatformConnection::factory()->for($user)->create(['platform' => SocialPlatform::Instagram]);

    $this->actingAs($user)->delete(route('connections.destroy', ['platform' => 'linkedin']));

    $remaining = $user->connections()->get();

    expect($remaining)->toHaveCount(1)
        ->and($remaining->first()->platform)->toBe(SocialPlatform::Instagram);
});
