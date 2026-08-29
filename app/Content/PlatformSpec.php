<?php

namespace App\Content;

use App\Enums\SocialPlatform;

final class PlatformSpec
{
    /**
     * Composer spec for a platform: character limit + hashtag guidance.
     *
     * @return array{charLimit: int, hashtagMin: int, hashtagMax: int}
     */
    public static function for(SocialPlatform $platform): array
    {
        return match ($platform) {
            SocialPlatform::Linkedin => ['charLimit' => 3000, 'hashtagMin' => 3, 'hashtagMax' => 5],
            default => ['charLimit' => 3000, 'hashtagMin' => 1, 'hashtagMax' => 5],
        };
    }
}
