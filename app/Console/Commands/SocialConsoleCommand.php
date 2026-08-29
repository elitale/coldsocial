<?php

namespace App\Console\Commands;

use App\Models\PlatformCredential;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use function Laravel\Prompts\select;
use function Laravel\Prompts\warning;

#[Signature('social')]
#[Description('Interactive console to manage social platform OAuth credentials.')]
class SocialConsoleCommand extends Command
{
    public function handle(): int
    {
        $this->info('coldsocial · social credentials console');

        // First run: skip the empty menu and help the admin add their first one.
        if (PlatformCredential::count() === 0) {
            warning("No social platform credentials yet — let's add your first.");
            $this->call('social:credential:set');
        }

        do {
            $action = select(
                label: 'What would you like to do?',
                options: [
                    'set' => 'Add / update credentials',
                    'test' => 'Test credentials',
                    'list' => 'List credentials',
                    'remove' => 'Remove credentials',
                    'exit' => 'Exit',
                ],
            );

            match ($action) {
                'set' => $this->call('social:credential:set'),
                'test' => $this->call('social:credential:test'),
                'list' => $this->call('social:credential:list'),
                'remove' => $this->call('social:credential:remove'),
                default => null,
            };
        } while ($action !== 'exit');

        return self::SUCCESS;
    }
}
