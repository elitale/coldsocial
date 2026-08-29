<?php

use App\Enums\SocialPlatform;
use App\Models\Persona;
use App\Models\PlatformConnection;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;

test('guests are redirected to login', function () {
    $this->get(route('connections.index'))->assertRedirect(route('login'));
});

test('the hub lists every platform with the right status', function () {
    $user = User::factory()->has(Persona::factory())->create();

    $this->actingAs($user)
        ->get(route('connections.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('connections/index')
            ->has('platforms', 5)
            ->where('platforms.0.key', 'linkedin')
            ->where('platforms.0.status', 'available')
            ->where('platforms.1.key', 'instagram')
            ->where('platforms.1.status', 'coming_soon')
            ->where('platforms.4.status', 'coming_soon')
        );
});

test('a connected platform shows as connected with the account name', function () {
    $user = User::factory()->has(Persona::factory())->create();
    PlatformConnection::factory()->for($user)->create([
        'platform' => SocialPlatform::Linkedin,
        'display_name' => 'Priya Founder',
    ]);

    $this->actingAs($user)
        ->get(route('connections.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('platforms.0.status', 'connected')
            ->where('platforms.0.accountName', 'Priya Founder')
        );
});

test('tokens are encrypted at rest and never sent to the client', function () {
    $user = User::factory()->has(Persona::factory())->create();
    $connection = PlatformConnection::factory()->for($user)->create([
        'platform' => SocialPlatform::Linkedin,
        'access_token' => 'super-secret-token',
    ]);

    $raw = DB::table('platform_connections')->where('id', $connection->id)->value('access_token');
    expect($raw)->not->toBe('super-secret-token')
        ->and($connection->fresh()->access_token)->toBe('super-secret-token');

    $this->actingAs($user)
        ->get(route('connections.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('platforms.0.status', 'connected')
            ->missing('platforms.0.accessToken')
        )
        ->assertDontSee('super-secret-token');
});

test('connecting LinkedIn redirects to the consent screen and stores state', function () {
    $user = User::factory()->has(Persona::factory())->create();

    $response = $this->actingAs($user)->get(route('connections.redirect', ['platform' => 'linkedin']));

    $response->assertRedirectContains('linkedin.com/oauth/v2/authorization');
    $response->assertSessionHas('linkedin_oauth_state');
});

test('coming-soon and unknown platforms cannot start OAuth', function () {
    $user = User::factory()->has(Persona::factory())->create();

    $this->actingAs($user)->get(route('connections.redirect', ['platform' => 'instagram']))->assertNotFound();
    $this->actingAs($user)->get('/connections/twitter/redirect')->assertNotFound();
});

test('the callback stores an encrypted connection for the user', function () {
    Http::fake([
        '*/accessToken' => Http::response([
            'access_token' => 'access-123',
            'refresh_token' => 'refresh-123',
            'expires_in' => 3600,
        ]),
        '*/userinfo' => Http::response([
            'sub' => 'li-abc',
            'name' => 'Priya Founder',
            'picture' => 'https://media.example.com/priya.jpg',
        ]),
    ]);

    $user = User::factory()->has(Persona::factory())->create();

    $this->actingAs($user)
        ->withSession(['linkedin_oauth_state' => 'state-xyz'])
        ->get('/connections/linkedin/callback?code=auth-code&state=state-xyz')
        ->assertRedirect(route('connections.index'))
        ->assertSessionHas('success');

    $connection = $user->connections()->where('platform', SocialPlatform::Linkedin)->first();

    expect($connection)->not->toBeNull()
        ->and($connection->external_id)->toBe('li-abc')
        ->and($connection->display_name)->toBe('Priya Founder')
        ->and($connection->access_token)->toBe('access-123')
        ->and($connection->refresh_token)->toBe('refresh-123');
});

test('a denied or tampered callback stores nothing', function () {
    $user = User::factory()->has(Persona::factory())->create();

    $this->actingAs($user)
        ->withSession(['linkedin_oauth_state' => 'state-xyz'])
        ->get('/connections/linkedin/callback?error=user_cancelled_login&state=state-xyz')
        ->assertRedirect(route('connections.index'))
        ->assertSessionHas('error');

    $this->actingAs($user)
        ->withSession(['linkedin_oauth_state' => 'state-xyz'])
        ->get('/connections/linkedin/callback?code=auth-code&state=WRONG')
        ->assertSessionHas('error');

    expect($user->connections()->count())->toBe(0);
});
