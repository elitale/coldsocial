<?php

namespace App\Console\Commands;

use App\Ai\ModelTester;
use App\Ai\ProviderRequestException;
use App\Models\AiModel;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('ai:model:test {identifier} {--provider= : Disambiguate by provider slug} {--capability= : Disambiguate by capability}')]
#[Description('Send a minimal real request to a model to confirm it works.')]
class AiModelTestCommand extends Command
{
    public function handle(ModelTester $tester): int
    {
        $query = AiModel::query()
            ->with('provider')
            ->where('identifier', $this->argument('identifier'));

        if ($provider = $this->option('provider')) {
            $query->whereRelation('provider', 'slug', $provider);
        }

        if ($capability = $this->option('capability')) {
            $query->where('capability', $capability);
        }

        $models = $query->get();

        if ($models->count() !== 1) {
            $this->error($models->isEmpty()
                ? 'No matching model found.'
                : 'Ambiguous — narrow it with --provider and/or --capability.');

            return self::FAILURE;
        }

        $model = $models->firstOrFail();

        if (! $tester->supports($model)) {
            $this->warn("Testing {$model->capability->value} models isn't wired up yet — only text and thinking for now.");

            return self::SUCCESS;
        }

        try {
            $this->info("Testing {$model->provider->slug}/{$model->identifier} ({$model->capability->value})…");
            $reply = $tester->test($model);
        } catch (ProviderRequestException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('✓ Model responded: '.Str::of($reply)->squish()->limit(160));

        return self::SUCCESS;
    }
}
