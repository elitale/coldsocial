<?php

use App\Enums\SocialPlatform;
use App\Models\PlatformCredential;

/**
 * @return array<string, string>
 */
function socialConsoleMenu(): array
{
    return [
        'set' => 'Add / update credentials',
        'test' => 'Test credentials',
        'enable' => 'Enable a platform',
        'disable' => 'Disable a platform',
        'list' => 'View credentials',
        'remove' => 'Remove credentials',
        'exit' => 'Exit',
    ];
}

test('the social console shows the menu and status without forcing credential entry', function () {
    $this->artisan('social')
        ->expectsChoice('What would you like to do?', 'exit', socialConsoleMenu())
        ->expectsOutputToContain('LinkedIn')
        ->assertSuccessful();

    // Nothing was force-added — the operator chose from the menu.
    expect(PlatformCredential::count())->toBe(0);
});

test('the social console surfaces credential state in its status', function () {
    PlatformCredential::factory()->disabled()->create(['platform' => SocialPlatform::Linkedin]);

    $this->artisan('social')
        ->expectsChoice('What would you like to do?', 'exit', socialConsoleMenu())
        ->expectsOutputToContain('disabled')
        ->assertSuccessful();
});
