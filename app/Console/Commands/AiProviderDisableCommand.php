<?php

namespace App\Console\Commands;

use App\Models\AiProvider;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('ai:provider:disable {slug}')]
#[Description('Disable an AI provider.')]
class AiProviderDisableCommand extends Command
{
    public function handle(): int
    {
        $provider = AiProvider::where('slug', $this->argument('slug'))->first();

        if (! $provider instanceof AiProvider) {
            $this->error("No provider found with slug \"{$this->argument('slug')}\".");

            return self::FAILURE;
        }

        $provider->update(['enabled' => false]);
        $this->info("Provider \"{$provider->slug}\" disabled.");

        return self::SUCCESS;
    }
}
