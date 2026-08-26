<?php

namespace App\Console\Commands;

use App\Models\AiModel;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('ai:model:list {--capability= : Filter by capability}')]
#[Description('List AI models and which is the default per capability.')]
class AiModelListCommand extends Command
{
    public function handle(): int
    {
        $query = AiModel::query()->with('provider')->orderBy('capability')->orderBy('identifier');

        if ($capability = $this->option('capability')) {
            $query->where('capability', $capability);
        }

        $models = $query->get();

        if ($models->isEmpty()) {
            $this->info('No models configured. Add one with `ai:model:add`.');

            return self::SUCCESS;
        }

        $this->table(
            ['Capability', 'Provider', 'Identifier', 'Default', 'Enabled'],
            $models->map(fn (AiModel $model): array => [
                $model->capability->value,
                $model->provider->slug,
                $model->identifier,
                $model->is_default ? '★' : '',
                $model->enabled ? 'yes' : 'no',
            ])->all(),
        );

        return self::SUCCESS;
    }
}
