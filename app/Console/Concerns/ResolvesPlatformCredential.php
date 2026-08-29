<?php

namespace App\Console\Concerns;

use App\Enums\SocialPlatform;
use App\Models\PlatformCredential;

use function Laravel\Prompts\select;

trait ResolvesPlatformCredential
{
    /**
     * Resolve a stored credential from the {platform} argument, or an
     * interactive picker when the argument is omitted.
     */
    private function resolveCredential(): ?PlatformCredential
    {
        $platform = $this->argument('platform');

        if ($platform === null && $this->input->isInteractive()) {
            $stored = PlatformCredential::orderBy('platform')->get();

            if ($stored->isEmpty()) {
                $this->warn('No stored credentials yet. Add some with: php artisan social:credential:set');

                return null;
            }

            $choice = select('Which platform?', $stored->mapWithKeys(
                fn (PlatformCredential $credential): array => [$credential->platform->value => $credential->platform->label()],
            )->all());

            return $stored->firstWhere('platform', SocialPlatform::from($choice));
        }

        if ($platform === null) {
            $this->error('Specify a platform.');

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
