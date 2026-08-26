<?php

namespace App\Console\Commands;

use App\Enums\AiCapability;
use App\Models\AiModel;
use App\Models\AiProvider;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

#[Signature('ai')]
#[Description('Interactive console to manage AI providers and models.')]
class AiConsoleCommand extends Command
{
    public function handle(): int
    {
        $this->info('coldsocial · AI provider console');

        do {
            $action = select(
                label: 'What would you like to do?',
                options: [
                    'provider:add' => 'Add a provider',
                    'model:add' => 'Add a model',
                    'model:default' => 'Set the default model for a capability',
                    'provider:list' => 'List providers',
                    'model:list' => 'List models',
                    'provider:toggle' => 'Enable or disable a provider',
                    'provider:remove' => 'Remove a provider',
                    'exit' => 'Exit',
                ],
                scroll: 10,
            );

            match ($action) {
                'provider:add' => $this->addProvider(),
                'model:add' => $this->addModel(),
                'model:default' => $this->setDefaultModel(),
                'provider:list' => $this->call('ai:provider:list'),
                'model:list' => $this->call('ai:model:list'),
                'provider:toggle' => $this->toggleProvider(),
                'provider:remove' => $this->removeProvider(),
                default => null,
            };
        } while ($action !== 'exit');

        return self::SUCCESS;
    }

    private function addProvider(): void
    {
        $options = [
            '--name' => text('Provider name', required: true),
            '--driver' => $this->pickDriver(),
            // Always pass the key (even empty) so the sub-command doesn't prompt again.
            '--key' => password('API key (optional)'),
        ];

        $baseUrl = text('Base URL (optional)', placeholder: 'https://…');

        if ($baseUrl !== '') {
            $options['--base-url'] = $baseUrl;
        }

        $this->call('ai:provider:add', $options);
    }

    private function addModel(): void
    {
        $slug = $this->pickProvider();

        if ($slug === null) {
            return;
        }

        $capability = select('Capability', $this->capabilityChoices());

        $options = [
            'provider' => $slug,
            '--identifier' => text('Model identifier', required: true, placeholder: 'gpt-4o'),
            '--capability' => $capability,
        ];

        if (confirm("Make this the default {$capability} model?", default: false)) {
            $options['--default'] = true;
        }

        $this->call('ai:model:add', $options);
    }

    private function setDefaultModel(): void
    {
        $capability = select('Capability', $this->capabilityChoices());
        $models = AiModel::with('provider')->where('capability', $capability)->get();

        if ($models->isEmpty()) {
            warning("No {$capability} models yet — add one first.");

            return;
        }

        $id = (int) select('Default model', $models->mapWithKeys(fn (AiModel $model): array => [
            (string) $model->id => "{$model->provider->slug} / {$model->identifier}".($model->is_default ? ' (current)' : ''),
        ])->all());

        $model = $models->firstWhere('id', $id);

        if (! $model instanceof AiModel) {
            return;
        }

        $this->call('ai:model:default', [
            'capability' => $capability,
            'identifier' => $model->identifier,
            '--provider' => $model->provider->slug,
        ]);
    }

    private function toggleProvider(): void
    {
        $slug = $this->pickProvider();

        if ($slug === null) {
            return;
        }

        $provider = AiProvider::where('slug', $slug)->firstOrFail();

        $this->call($provider->enabled ? 'ai:provider:disable' : 'ai:provider:enable', ['slug' => $slug]);
    }

    private function removeProvider(): void
    {
        $slug = $this->pickProvider();

        if ($slug === null) {
            return;
        }

        if (! confirm("Remove provider \"{$slug}\" and all of its models?", default: false)) {
            return;
        }

        $this->call('ai:provider:remove', ['slug' => $slug, '--force' => true]);
    }

    private function pickDriver(): string
    {
        $driver = (string) select('Driver', [
            'openai' => 'OpenAI (OpenAI-compatible)',
            'openrouter' => 'OpenRouter',
            'github' => 'GitHub Models / Copilot',
            'anthropic' => 'Anthropic',
            'gemini' => 'Google Gemini',
            'other' => 'Other…',
        ]);

        return $driver === 'other' ? text('Driver key', required: true) : $driver;
    }

    /**
     * @return string|null the chosen provider slug, or null when there are none
     */
    private function pickProvider(): ?string
    {
        $providers = AiProvider::orderBy('name')->get();

        if ($providers->isEmpty()) {
            warning('No providers yet — add one first.');

            return null;
        }

        return (string) select('Provider', $providers->mapWithKeys(fn (AiProvider $provider): array => [
            $provider->slug => $provider->name.($provider->enabled ? '' : ' (disabled)'),
        ])->all());
    }

    /**
     * @return array<string, string>
     */
    private function capabilityChoices(): array
    {
        return collect(AiCapability::cases())
            ->mapWithKeys(fn (AiCapability $capability): array => [$capability->value => ucfirst($capability->value)])
            ->all();
    }
}
