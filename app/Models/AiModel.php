<?php

namespace App\Models;

use App\Enums\AiCapability;
use Database\Factories\AiModelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $ai_provider_id
 * @property string $identifier
 * @property string|null $label
 * @property AiCapability $capability
 * @property bool $enabled
 * @property bool $is_default
 * @property array<string, mixed>|null $settings
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read AiProvider $provider
 */
#[Fillable(['ai_provider_id', 'identifier', 'label', 'capability', 'enabled', 'is_default', 'settings'])]
class AiModel extends Model
{
    /** @use HasFactory<AiModelFactory> */
    use HasFactory;

    /**
     * Keep at most one default model per capability: when a model is saved as the
     * default, clear the flag on every other model of the same capability.
     */
    protected static function booted(): void
    {
        static::saved(function (AiModel $model): void {
            if (! $model->is_default) {
                return;
            }

            static::query()
                ->where('capability', $model->capability->value)
                ->whereKeyNot($model->getKey())
                ->where('is_default', true)
                ->update(['is_default' => false]);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capability' => AiCapability::class,
            'enabled' => 'boolean',
            'is_default' => 'boolean',
            'settings' => 'array',
        ];
    }

    /**
     * @return BelongsTo<AiProvider, $this>
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'ai_provider_id');
    }
}
