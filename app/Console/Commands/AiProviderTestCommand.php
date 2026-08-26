<?php

namespace App\Console\Commands;

use App\Ai\ModelCatalog;
use App\Ai\ProviderRequestException;
use App\Models\AiProvider;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('ai:provider:test {slug}')]
#[Description('Verify a provider API key by listing its available models.')]
class AiProviderTestCommand extends Command
{
    public function handle(ModelCatalog $catalog): int
    {
        $provider = AiProvider::where('slug', $this->argument('slug'))->first();

        if (! $provider instanceof AiProvider) {
            $this->error("No provider found with slug \"{$this->argument('slug')}\".");

            return self::FAILURE;
        }

        if (! $catalog->supports($provider)) {
            $this->warn("Listing models isn't supported for the \"{$provider->driver}\" driver yet.");

            return self::SUCCESS;
        }

        try {
            $models = $catalog->models($provider);
        } catch (ProviderRequestException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("✓ {$provider->name}: key accepted — ".count($models).' model(s) available.');

        return self::SUCCESS;
    }
}
