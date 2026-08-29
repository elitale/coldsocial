<?php

namespace App\Console\Commands;

use App\Enums\SocialPlatform;
use App\Models\PlatformCredential;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;

#[Signature('social:credential:remove {platform? : Platform key} {--force : Skip the confirmation}')]
#[Description('Remove stored credentials for a platform.')]
class SocialCredentialRemoveCommand extends Command
{
    public function handle(): int
    {
        $credential = $this->resolveCredential();

        if (! $credential instanceof PlatformCredential) {
            return self::FAILURE;
        }

        if (! $this->option('force') && $this->input->isInteractive()
            && ! confirm("Remove {$credential->platform->label()} credentials?", default: false)) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        $label = $credential->platform->label();
        $credential->delete();

        $this->info("Removed {$label} credentials.");

        return self::SUCCESS;
    }

    private function resolveCredential(): ?PlatformCredential
    {
        $platform = $this->argument('platform');

        if ($platform === null && $this->input->isInteractive()) {
            $stored = PlatformCredential::orderBy('platform')->get();

            if ($stored->isEmpty()) {
                $this->warn('No stored credentials to remove.');

                return null;
            }

            $choice = select('Remove which platform?', $stored->mapWithKeys(
                fn (PlatformCredential $credential): array => [$credential->platform->value => $credential->platform->label()],
            )->all());

            return $stored->firstWhere('platform', SocialPlatform::from($choice));
        }

        if ($platform === null) {
            $this->error('Specify a platform to remove.');

            return null;
        }

        $case = SocialPlatform::tryFrom((string) $platform);

        if (! $case instanceof SocialPlatform) {
            $this->error("Unknown platform \"{$platform}\".");

            return null;
        }

        $credential = PlatformCredential::where('platform', $case)->first();

        if (! $credential instanceof PlatformCredential) {
            $this->error("No stored credentials for {$case->label()}.");

            return null;
        }

        return $credential;
    }
}
