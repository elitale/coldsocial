<?php

namespace App\Console\Commands;

use App\Console\Concerns\ResolvesPlatformCredential;
use App\Models\PlatformCredential;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('social:credential:enable {platform? : Platform key}')]
#[Description('Enable a configured platform so its accounts can be connected.')]
class SocialCredentialEnableCommand extends Command
{
    use ResolvesPlatformCredential;

    public function handle(): int
    {
        $credential = $this->resolveCredential();

        if (! $credential instanceof PlatformCredential) {
            return self::FAILURE;
        }

        $credential->update(['enabled' => true]);

        $this->info("Enabled {$credential->platform->label()}.");

        return self::SUCCESS;
    }
}
