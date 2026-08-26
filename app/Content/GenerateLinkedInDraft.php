<?php

namespace App\Content;

use App\Ai\ProviderRequestException;
use App\Ai\TextGenerator;
use App\Models\Persona;
use App\Models\Post;
use App\Models\Update;

class GenerateLinkedInDraft
{
    public function __construct(private readonly TextGenerator $generator) {}

    /**
     * Generate a LinkedIn draft from an update (in the author's persona voice) and store it.
     *
     * @throws ProviderRequestException when no text model responds
     */
    public function for(Update $update): Post
    {
        $body = $this->generator->generate($this->prompt($update), maxTokens: 600);

        return $update->user->posts()->create([
            'update_id' => $update->id,
            'platform' => 'linkedin',
            'body' => $body,
        ]);
    }

    private function prompt(Update $update): string
    {
        $lines = ['Write a single LinkedIn post based on the update below.'];

        foreach ($this->voiceHints($update->user->persona) as $hint) {
            $lines[] = $hint;
        }

        $lines[] = 'Update: '.$update->body;

        if ($update->source_url !== null) {
            $lines[] = 'Source: '.$update->source_url;
        }

        $lines[] = 'Return only the post text, ready to publish — authentic and engaging for LinkedIn.';

        return implode("\n", $lines);
    }

    /**
     * Compact voice cues drawn from the persona, skipping anything not set.
     *
     * @return list<string>
     */
    private function voiceHints(?Persona $persona): array
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
