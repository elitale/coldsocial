<?php

namespace App\Console\Commands;

use App\Ai\ModelCatalog;
use App\Ai\ProviderRequestException;
use App\Enums\AiCapability;
use App\Models\AiModel;
use App\Models\AiProvider;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

#[Signature('ai')]
#[Description('Interactive console to manage AI providers and models.')]
class AiConsoleCommand extends Command
{
    /**
     * Known provider presets — pick one and we fill in the driver.
     *
     * @var array<string, array{name: string, driver: string}>
     */
    private const PRESETS = [
        'openai' => ['name' => 'OpenAI', 'driver' => 'openai'],
        'openrouter' => ['name' => 'OpenRouter', 'driver' => 'openrouter'],
        'anthropic' => ['name' => 'Anthropic', 'driver' => 'anthropic'],
        'gemini' => ['name' => 'Google Gemini', 'driver' => 'gemini'],
        'github' => ['name' => 'GitHub Models', 'driver' => 'github'],
    ];

    public function handle(): int
    {
        $this->info('coldsocial · AI provider console');

        // First run: skip the empty menu and help the admin add their first provider.
        if (AiProvider::count() === 0) {
            warning("No AI providers yet — let's add your first one.");
            $this->addProvider();
        }

        do {
            $this->showStatus();

            $action = select(
                label: 'What would you like to do?',
                options: [
                    'provider:add' => 'Add a provider',
                    'model:add' => 'Add a model',
                    'model:default' => 'Set the default model for a capability',
                    'provider:list' => 'List providers',
                    'model:list' => 'List models',
                    'model:test' => 'Test a model',
                    'provider:test' => 'Test a provider connection',
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
                'model:test' => $this->testModel(),
                'provider:test' => $this->testProvider(),
                'provider:toggle' => $this->toggleProvider(),
                'provider:remove' => $this->removeProvider(),
                default => null,
            };
        } while ($action !== 'exit');

        return self::SUCCESS;
    }

    private function showStatus(): void
    {
        $providers = AiProvider::query()->get(['id', 'enabled']);

        if ($providers->isEmpty()) {
            return;
        }

        $enabled = $providers->where('enabled', true)->count();

        $defaults = AiModel::query()
            ->with('provider')
            ->where('is_default', true)
            ->get()
            ->map(fn (AiModel $model): string => "{$model->capability->value}: {$model->provider->slug}/{$model->identifier}")
            ->all();

        $this->newLine();
        $this->line("  Providers: {$providers->count()} ({$enabled} enabled)");
        $this->line('  Default models: '.($defaults === [] ? 'none set' : implode(', ', $defaults)));
        $this->newLine();
    }

    private function addProvider(): void
    {
        $presetOptions = [];

        foreach (self::PRESETS as $key => $preset) {
            $presetOptions[$key] = $preset['name'];
        }

        $presetOptions['custom'] = 'Custom (enter details manually)';
        $presetOptions['__cancel__'] = '← Cancel';

        $preset = (string) select('Which provider?', $presetOptions);

        if ($preset === '__cancel__') {
            return;
        }

        if ($preset === 'custom') {
            $driver = $this->pickDriver();
            $baseUrl = text('Base URL (optional)', placeholder: 'https://…');
            $name = text('Provider name', required: true);
        } else {
            $config = self::PRESETS[$preset];
            $driver = $config['driver'];
            $baseUrl = '';
            $name = text('A name for this provider', default: $config['name'], required: true);
        }

        $options = [
            '--name' => $name,
            '--driver' => $driver,
            // Always pass the key (even empty) so the sub-command doesn't prompt again.
            '--key' => password('API key'),
        ];

        if ($baseUrl !== '') {
            $options['--base-url'] = $baseUrl;
        }

        if ($this->call('ai:provider:add', $options) === self::SUCCESS) {
            $this->offerNextStepsForProvider($name);
        }
    }

    private function offerNextStepsForProvider(string $name): void
    {
        $provider = AiProvider::where('slug', Str::slug($name))->first();

        if (! $provider instanceof AiProvider) {
            return;
        }

        if (confirm('Test the connection now?', default: true)) {
            $this->call('ai:provider:test', ['slug' => $provider->slug]);
        }

        if (confirm('Add a model from this provider now?', default: true)) {
            $this->addModelFor($provider);
        }
    }

    private function addModel(): void
    {
        $slug = $this->pickProvider();

        if ($slug === null) {
            return;
        }

        $this->addModelFor(AiProvider::where('slug', $slug)->firstOrFail());
    }

    private function addModelFor(AiProvider $provider): void
    {
        $capability = (string) select('Capability', $this->capabilityChoices());
        $identifier = $this->pickModelIdentifier($provider);

        $options = [
            'provider' => $provider->slug,
            '--identifier' => $identifier,
            '--capability' => $capability,
        ];

        if (confirm("Make this the default {$capability} model?", default: false)) {
            $options['--default'] = true;
        }

        if ($this->call('ai:model:add', $options) !== self::SUCCESS) {
            return;
        }

        $textual = in_array($capability, [AiCapability::Text->value, AiCapability::Thinking->value], true);

        if ($textual && confirm('Test this model now?', default: false)) {
            $this->call('ai:model:test', [
                'identifier' => $identifier,
                '--provider' => $provider->slug,
                '--capability' => $capability,
            ]);
        }
    }

    private function setDefaultModel(): void
    {
        $capability = select('Capability', $this->capabilityChoices());
        $models = AiModel::with('provider')->where('capability', $capability)->get();

        if ($models->isEmpty()) {
            warning("No {$capability} models yet — add one first.");

            return;
        }

        $options = $models->mapWithKeys(fn (AiModel $model): array => [
            (string) $model->id => "{$model->provider->slug} / {$model->identifier}".($model->is_default ? ' (current)' : ''),
        ])->all();
        $options['__cancel__'] = '← Cancel';

        $choice = (string) select('Default model', $options);

        if ($choice === '__cancel__') {
            return;
        }

        $model = $models->firstWhere('id', (int) $choice);

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

    private function pickModelIdentifier(AiProvider $provider): string
    {
        $catalog = app(ModelCatalog::class);

        if ($catalog->supports($provider)) {
            try {
                $this->info("Verifying key and fetching models from {$provider->name}…");
                $models = $catalog->models($provider);
            } catch (ProviderRequestException $e) {
                warning($e->getMessage());
                $models = [];
            }

            if ($models !== []) {
                $manual = '__manual__';
                $choice = (string) search(
                    label: 'Model (type to filter)',
                    options: fn (string $value): array => $this->filterModels($models, $value) + [$manual => 'Enter manually…'],
                    scroll: 15,
                );

                if ($choice !== $manual) {
                    return $choice;
                }
            }
        }

        return text('Model identifier', required: true, placeholder: 'gpt-4o');
    }

    /**
     * @param  list<string>  $models
     * @return array<string, string>
     */
    private function filterModels(array $models, string $value): array
    {
        $matches = $value === ''
            ? $models
            : array_values(array_filter(
                $models,
                fn (string $model): bool => str_contains(strtolower($model), strtolower($value)),
            ));

        return array_combine($matches, $matches);
    }

    private function testProvider(): void
    {
        $slug = $this->pickProvider();

        if ($slug === null) {
            return;
        }

        $this->call('ai:provider:test', ['slug' => $slug]);
    }

    private function testModel(): void
    {
        $models = AiModel::with('provider')->orderBy('capability')->orderBy('identifier')->get();

        if ($models->isEmpty()) {
            warning('No models yet — add one first.');

            return;
        }

        $options = $models->mapWithKeys(fn (AiModel $model): array => [
            (string) $model->id => "{$model->provider->slug} / {$model->identifier} ({$model->capability->value})",
        ])->all();
        $options['__cancel__'] = '← Cancel';

        $choice = (string) select(label: 'Model to test', options: $options, scroll: 15);

        if ($choice === '__cancel__') {
            return;
        }

        $model = $models->firstWhere('id', (int) $choice);

        if (! $model instanceof AiModel) {
            return;
        }

        $this->call('ai:model:test', [
            'identifier' => $model->identifier,
            '--provider' => $model->provider->slug,
            '--capability' => $model->capability->value,
        ]);
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

        $options = $providers->mapWithKeys(fn (AiProvider $provider): array => [
            $provider->slug => $provider->name.($provider->enabled ? '' : ' (disabled)'),
        ])->all();
        $options['__cancel__'] = '← Cancel';

        $choice = (string) select('Provider', $options);

        return $choice === '__cancel__' ? null : $choice;
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
