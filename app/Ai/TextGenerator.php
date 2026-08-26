<?php

namespace App\Ai;

use App\Enums\AiCapability;
use App\Models\AiModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Turns a prompt into text using the configured AI layer: it tries the default text model
 * first, then falls back through the other enabled text models until one responds.
 */
class TextGenerator
{
    public function __construct(private readonly OpenAiCompatibleChat $chat) {}

    /**
     * @throws ProviderRequestException when no text model is configured or every candidate fails
     */
    public function generate(string $prompt, int $maxTokens = 512): string
    {
        $candidates = $this->candidates();

        if ($candidates->isEmpty()) {
            throw new ProviderRequestException('No enabled text model is configured. Add one with `php artisan ai`.');
        }

        $lastError = null;

        foreach ($candidates as $model) {
            try {
                return $this->chat->complete($model, $prompt, $maxTokens);
            } catch (ProviderRequestException $e) {
                $lastError = $e;
                Log::warning('Text generation fell back to the next model.', [
                    'provider' => $model->provider->slug,
                    'model' => $model->identifier,
                    'reason' => $e->getMessage(),
                ]);
            }
        }

        throw new ProviderRequestException('Every configured text model failed to respond.', previous: $lastError);
    }

    /**
     * Enabled text models from enabled providers, default first then stable by id.
     *
     * @return Collection<int, AiModel>
     */
    private function candidates(): Collection
    {
        return AiModel::query()
            ->where('capability', AiCapability::Text->value)
            ->where('enabled', true)
            ->whereHas('provider', fn (Builder $query): Builder => $query->where('enabled', true))
            ->with('provider')
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();
    }
}
