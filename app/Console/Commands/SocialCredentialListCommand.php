<?php

namespace App\Console\Commands;

use App\Models\PlatformCredential;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('social:credential:list')]
#[Description('List stored social platform credentials and their last test result.')]
class SocialCredentialListCommand extends Command
{
    public function handle(): int
    {
        $credentials = PlatformCredential::orderBy('platform')->get();

        if ($credentials->isEmpty()) {
            $this->warn('No social platform credentials stored yet. Add one with: php artisan social:credential:set');

            return self::SUCCESS;
        }

        $this->table(
            ['Platform', 'Client ID', 'Redirect URL', 'Last tested', 'Result'],
            $credentials->map(fn (PlatformCredential $credential): array => [
                $credential->platform->label(),
                $credential->client_id,
                $credential->redirect_url ?? '—',
                $credential->last_tested_at?->diffForHumans() ?? 'never',
                $this->result($credential),
            ])->all(),
        );

        return self::SUCCESS;
    }

    private function result(PlatformCredential $credential): string
    {
        return match ($credential->test_passed) {
            true => 'passed',
            false => 'failed',
            null => 'untested',
        };
    }
}
