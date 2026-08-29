<?php

namespace App\Console\Commands;

use App\Connections\LinkedInOAuth;
use App\Enums\SocialPlatform;
use App\Models\PlatformCredential;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

#[Signature('social:credential:test {platform? : Platform key; omit to test every stored credential}')]
#[Description('Test stored social platform OAuth credentials against the provider.')]
class SocialCredentialTestCommand extends Command
{
    public function handle(LinkedInOAuth $linkedin): int
    {
        $credentials = $this->credentialsToTest();

        if ($credentials === null) {
            return self::FAILURE;
        }

        if ($credentials->isEmpty()) {
            $this->warn('No stored credentials to test. Add some with: php artisan social:credential:set');

            return self::SUCCESS;
        }

        $allPassed = true;

        foreach ($credentials as $credential) {
            $result = $linkedin->testCredentials($credential->client_id, $credential->client_secret);

            $credential->update([
                'last_tested_at' => now(),
                'test_passed' => $result['passed'],
                'test_message' => $result['message'],
            ]);

            $label = $credential->platform->label();

            if ($result['passed']) {
                $this->info("✓ {$label}: {$result['message']}");
            } else {
                $this->error("✗ {$label}: {$result['message']}");
                $allPassed = false;
            }
        }

        return $allPassed ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @return Collection<int, PlatformCredential>|null
     */
    private function credentialsToTest(): ?Collection
    {
        $platform = $this->argument('platform');

        if ($platform === null) {
            return PlatformCredential::all();
        }

        $case = SocialPlatform::tryFrom((string) $platform);

        if (! $case instanceof SocialPlatform) {
            $this->error("Unknown platform \"{$platform}\".");

            return null;
        }

        return PlatformCredential::where('platform', $case)->get();
    }
}
