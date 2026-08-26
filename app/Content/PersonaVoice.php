<?php

namespace App\Content;

use App\Models\Persona;

class PersonaVoice
{
    /**
     * Compact voice cues drawn from the persona, skipping anything not set.
     *
     * @return list<string>
     */
    public static function hints(?Persona $persona): array
    {
        if ($persona === null) {
            return [];
        }

        $hints = [];

        if (is_string($persona->headline) && $persona->headline !== '') {
            $hints[] = "Author: {$persona->headline}.";
        }

        if (is_string($persona->formality) && $persona->formality !== '') {
            $hints[] = "Formality: {$persona->formality}.";
        }

        if (is_string($persona->emoji_usage) && $persona->emoji_usage !== '') {
            $hints[] = "Emoji usage: {$persona->emoji_usage}.";
        }

        foreach (['Tone' => $persona->tones, 'Audience' => $persona->audiences] as $label => $values) {
            $strings = array_filter((array) $values, 'is_string');

            if ($strings !== []) {
                $hints[] = "{$label}: ".implode(', ', $strings).'.';
            }
        }

        return $hints;
    }
}
