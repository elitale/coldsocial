<?php

use App\Models\Persona;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia;

test('guests are redirected to login', function () {
    $this->get(route('calendar.index'))->assertRedirect(route('login'));
});

test('a scheduled post appears on its day', function () {
    $user = User::factory()->has(Persona::factory())->create(['timezone' => 'UTC']);
    $post = Post::factory()->for($user)->scheduled()->create([
        'platform' => 'linkedin',
        'scheduled_at' => Carbon::parse('2099-08-15 09:00', 'UTC'),
    ]);

    $this->actingAs($user)
        ->get(route('calendar.index', ['month' => '2099-08']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('calendar/index')
            ->where('month', '2099-08')
            ->where('timezone', 'UTC')
            ->has('postsByDay.2099-08-15', 1)
            ->where('postsByDay.2099-08-15.0.id', $post->id)
            ->where('postsByDay.2099-08-15.0.platform', 'linkedin')
            ->where('postsByDay.2099-08-15.0.time', '09:00')
        );
});

test('posts in other months are not shown', function () {
    $user = User::factory()->has(Persona::factory())->create(['timezone' => 'UTC']);
    Post::factory()->for($user)->scheduled()->create([
        'scheduled_at' => Carbon::parse('2099-09-15 09:00', 'UTC'),
    ]);

    $this->actingAs($user)
        ->get(route('calendar.index', ['month' => '2099-08']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('month', '2099-08')
            ->where('postsByDay', [])
        );
});

test('a post is placed on the day it goes out in the user timezone', function () {
    $user = User::factory()->has(Persona::factory())->create(['timezone' => 'Asia/Kolkata']);
    // 2099-08-31 20:00 UTC == 2099-09-01 01:30 in Asia/Kolkata.
    $post = Post::factory()->for($user)->scheduled()->create([
        'scheduled_at' => Carbon::parse('2099-08-31 20:00', 'UTC'),
    ]);

    // Not in August (local calendar).
    $this->actingAs($user)
        ->get(route('calendar.index', ['month' => '2099-08']))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('postsByDay', []));

    // In September, on the 1st, at the local time.
    $this->actingAs($user)
        ->get(route('calendar.index', ['month' => '2099-09']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('postsByDay.2099-09-01', 1)
            ->where('postsByDay.2099-09-01.0.id', $post->id)
            ->where('postsByDay.2099-09-01.0.time', '01:30')
        );
});

test('only the user\'s own scheduled posts appear', function () {
    $user = User::factory()->has(Persona::factory())->create(['timezone' => 'UTC']);
    $other = User::factory()->has(Persona::factory())->create();
    Post::factory()->for($other)->scheduled()->create([
        'scheduled_at' => Carbon::parse('2099-08-15 09:00', 'UTC'),
    ]);

    $this->actingAs($user)
        ->get(route('calendar.index', ['month' => '2099-08']))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('postsByDay', []));
});

test('unscheduled drafts and approved posts are not shown', function () {
    $user = User::factory()->has(Persona::factory())->create(['timezone' => 'UTC']);
    Post::factory()->for($user)->approved()->create();
    Post::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('calendar.index', ['month' => '2099-08']))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('postsByDay', []));
});
