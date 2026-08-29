<?php

namespace App\Enums;

enum SocialPlatform: string
{
    case Linkedin = 'linkedin';
    case Instagram = 'instagram';
    case Tiktok = 'tiktok';
    case Youtube = 'youtube';
    case Facebook = 'facebook';

    /**
     * Human-facing platform name.
     */
    public function label(): string
    {
        return match ($this) {
            self::Linkedin => 'LinkedIn',
            self::Instagram => 'Instagram',
            self::Tiktok => 'TikTok',
            self::Youtube => 'YouTube',
            self::Facebook => 'Facebook',
        };
    }

    /**
     * Whether this platform has a working OAuth connect flow yet. Others render
     * as "Coming soon" until their driver + credentials exist.
     */
    public function connectable(): bool
    {
        return $this === self::Linkedin;
    }
}
