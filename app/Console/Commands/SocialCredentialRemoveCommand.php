<?php

namespace App\Console\Commands;

use App\Console\Concerns\ResolvesPlatformCredential;
use App\Models\PlatformCredential;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;

#[Signature('social:credential:remove {platform? : Platform key} {--force : Skip the confirmation}')]
#[Description('Remove stored credentials for a platform.')]
class SocialCredentialRemoveCommand extends Command
{
    use ResolvesPlatformCredential;

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
}
