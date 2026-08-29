<?php

namespace App\Models;

use App\Enums\SocialPlatform;
use Database\Factories\PlatformCredentialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property SocialPlatform $platform
 * @property string $client_id
 * @property string $client_secret
 * @property string|null $redirect_url
 * @property Carbon|null $last_tested_at
 * @property bool|null $test_passed
 * @property string|null $test_message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['platform', 'client_id', 'client_secret', 'redirect_url', 'last_tested_at', 'test_passed', 'test_message'])]
#[Hidden(['client_secret'])]
class PlatformCredential extends Model
{
    /** @use HasFactory<PlatformCredentialFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'platform' => SocialPlatform::class,
            'client_secret' => 'encrypted',
            'last_tested_at' => 'datetime',
            'test_passed' => 'boolean',
        ];
    }
}
