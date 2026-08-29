<?php

namespace App\Console\Commands;

use App\Console\Concerns\ResolvesPlatformCredential;
use App\Models\PlatformCredential;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('social:credential:disable {platform? : Platform key}')]
#[Description('Disable a platform so it can no longer be connected (credentials are kept).')]
class SocialCredentialDisableCommand extends Command
{
    use ResolvesPlatformCredential;

    public function handle(): int
    {
        $credential = $this->resolveCredential();

        if (! $credential instanceof PlatformCredential) {
            return self::FAILURE;
        }

        $credential->update(['enabled' => false]);

        $this->info("Disabled {$credential->platform->label()}.");

        return self::SUCCESS;
    }
}
