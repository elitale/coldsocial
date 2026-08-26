<?php

namespace App\Console\Commands;

use App\Enums\AiCapability;
use App\Models\AiProvider;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

#[Signature('ai:model:add {provider? : Provider slug} {--identifier=} {--label=} {--capability=} {--default : Make this the default for its capability} {--disabled}')]
#[Description('Add a model to a provider.')]
class AiModelAddCommand extends Command
{
    public function handle(): int
    {
        $slug = $this->argument('provider') ?: text('Provider slug', required: true);
        $provider = AiProvider::where('slug', $slug)->first();

        if (! $provider instanceof AiProvider) {
            $this->error("No provider found with slug \"{$slug}\".");

            return self::FAILURE;
        }

        $identifier = $this->option('identifier') ?: text('Model identifier (e.g. gpt-4o)', required: true);
        $choices = array_map(fn (AiCapability $capability): string => $capability->value, AiCapability::cases());
        $capabilityValue = $this->option('capability') ?: select('Capability', $choices);
        $capability = AiCapability::tryFrom((string) $capabilityValue);

        if (! $capability instanceof AiCapability) {
            $this->error('Invalid capability. Valid: '.implode(', ', $choices));

            return self::FAILURE;
        }

        if ($provider->models()->where('identifier', $identifier)->where('capability', $capability->value)->exists()) {
            $this->error("Model \"{$identifier}\" ({$capability->value}) already exists for {$provider->slug}.");

            return self::FAILURE;
        }

        $model = $provider->models()->create([
            'identifier' => $identifier,
            'label' => $this->option('label') ?: null,
            'capability' => $capability,
            'enabled' => ! $this->option('disabled'),
            'is_default' => (bool) $this->option('default'),
        ]);

        $this->info("Model \"{$model->identifier}\" ({$capability->value}) added to {$provider->slug}".($model->is_default ? ' as the default.' : '.'));

        return self::SUCCESS;
    }
}
