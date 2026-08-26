<?php

namespace App\Console\Commands;

use App\Models\AiProvider;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('ai:provider:list')]
#[Description('List configured AI providers. Secrets are never shown.')]
class AiProviderListCommand extends Command
{
    public function handle(): int
    {
        $providers = AiProvider::query()->withCount('models')->orderBy('name')->get();

        if ($providers->isEmpty()) {
            $this->info('No AI providers configured. Add one with `ai:provider:add`.');

            return self::SUCCESS;
        }

        $this->table(
            ['Slug', 'Name', 'Driver', 'Enabled', 'Models', 'Key'],
            $providers->map(fn (AiProvider $provider): array => [
                $provider->slug,
                $provider->name,
                $provider->driver,
                $provider->enabled ? 'yes' : 'no',
                $provider->getAttribute('models_count'),
                $provider->api_key ? 'set' : '—',
            ])->all(),
        );

        return self::SUCCESS;
    }
}
