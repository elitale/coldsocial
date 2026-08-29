<?php

namespace App\Console\Commands;

use App\Enums\SocialPlatform;
use App\Models\PlatformCredential;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

use function Laravel\Prompts\select;

#[Signature('social')]
#[Description('Interactive console to manage social platform OAuth credentials.')]
class SocialConsoleCommand extends Command
{
    public function handle(): int
    {
        $this->info('coldsocial · social credentials console');

        do {
            $this->newLine();
            $this->showStatus();

            $action = select(
                label: 'What would you like to do?',
                options: [
                    'set' => 'Add / update credentials',
                    'test' => 'Test credentials',
                    'enable' => 'Enable a platform',
                    'disable' => 'Disable a platform',
                    'list' => 'View credentials',
                    'remove' => 'Remove credentials',
                    'exit' => 'Exit',
                ],
                scroll: 10,
            );

            match ($action) {
                'set' => $this->call('social:credential:set'),
                'test' => $this->call('social:credential:test'),
                'enable' => $this->call('social:credential:enable'),
                'disable' => $this->call('social:credential:disable'),
                'list' => $this->call('social:credential:list'),
                'remove' => $this->call('social:credential:remove'),
                default => null,
            };
        } while ($action !== 'exit');

        return self::SUCCESS;
    }

    private function showStatus(): void
    {
        $credentials = $this->credentials();

        $rows = array_map(function (SocialPlatform $platform) use ($credentials): array {
            $credential = $credentials->get($platform->value);

            return [
                $platform->label(),
                $platform->connectable() ? 'yes' : 'coming soon',
                $credential === null ? '—' : ($credential->enabled ? 'enabled' : 'disabled'),
                $this->testStatus($credential),
            ];
        }, SocialPlatform::cases());

        $this->table(['Platform', 'Connectable', 'Credentials', 'Last test'], $rows);
    }

    /**
     * @return Collection<string, PlatformCredential>
     */
    private function credentials(): Collection
    {
        return PlatformCredential::all()
            ->keyBy(fn (PlatformCredential $credential): string => $credential->platform->value);
    }

    private function testStatus(?PlatformCredential $credential): string
    {
        if ($credential === null || $credential->last_tested_at === null) {
            return 'never';
        }

        return ($credential->test_passed ? 'passed' : 'failed').' '.$credential->last_tested_at->diffForHumans();
    }
}
