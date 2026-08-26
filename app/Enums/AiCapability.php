<?php

namespace App\Enums;

enum AiCapability: string
{
    case Text = 'text';
    case Thinking = 'thinking';
    case Image = 'image';
    case Video = 'video';
    case Tts = 'tts';
    case Stt = 'stt';
}
