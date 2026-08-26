<?php

namespace App\Console\Commands;

use App\Models\AiProvider;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;

#[Signature('ai:provider:remove {slug} {--force : Skip the confirmation}')]
#[Description('Remove an AI provider and all of its models.')]
class AiProviderRemoveCommand extends Command
{
    public function handle(): int
    {
        $provider = AiProvider::where('slug', $this->argument('slug'))->first();

        if (! $provider instanceof AiProvider) {
            $this->error("No provider found with slug \"{$this->argument('slug')}\".");

            return self::FAILURE;
        }

        if (! $this->option('force') && ! confirm("Remove provider \"{$provider->slug}\" and all of its models?", default: false)) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        $provider->delete();
        $this->info("Provider \"{$provider->slug}\" removed.");

        return self::SUCCESS;
    }
}
