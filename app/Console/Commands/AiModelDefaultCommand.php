<?php

namespace App\Console\Commands;

use App\Enums\AiCapability;
use App\Models\AiModel;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('ai:model:default {capability} {identifier} {--provider= : Disambiguate when the identifier exists under multiple providers}')]
#[Description('Set the default model for a capability.')]
class AiModelDefaultCommand extends Command
{
    public function handle(): int
    {
        $choices = array_map(fn (AiCapability $capability): string => $capability->value, AiCapability::cases());
        $capability = AiCapability::tryFrom((string) $this->argument('capability'));

        if (! $capability instanceof AiCapability) {
            $this->error('Invalid capability. Valid: '.implode(', ', $choices));

            return self::FAILURE;
        }

        $query = AiModel::query()
            ->where('capability', $capability->value)
            ->where('identifier', $this->argument('identifier'));

        if ($providerSlug = $this->option('provider')) {
            $query->whereRelation('provider', 'slug', $providerSlug);
        }

        $models = $query->get();

        if ($models->count() !== 1) {
            $this->error($models->isEmpty()
                ? 'No matching model found.'
                : 'Ambiguous — that identifier exists under multiple providers. Pass --provider=<slug>.');

            return self::FAILURE;
        }

        $model = $models->firstOrFail();
        $model->update(['is_default' => true]);

        $this->info("Default {$capability->value} model set to {$model->provider->slug}/{$model->identifier}.");

        return self::SUCCESS;
    }
}
