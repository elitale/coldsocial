<?php

namespace App\Content;

use App\Ai\ProviderRequestException;
use App\Ai\TextGenerator;
use App\Models\User;

class GenerateStudioDraft
{
    public function __construct(private readonly TextGenerator $generator) {}

    /**
     * Compose a LinkedIn caption (with inline hashtags) in the user's persona voice.
     *
     * @throws ProviderRequestException when no text model responds
     */
    public function for(User $user, ?string $prompt = null): string
    {
        return $this->generator->generate($this->prompt($user, $prompt), maxTokens: 600);
    }

    private function prompt(User $user, ?string $prompt): string
    {
        $lines = ['Write a single LinkedIn post.'];

        foreach (PersonaVoice::hints($user->persona) as $hint) {
            $lines[] = $hint;
        }

        if ($prompt !== null && trim($prompt) !== '') {
            $lines[] = 'Topic: '.$prompt;
        }

        $lines[] = 'End with 3-5 relevant hashtags.';
        $lines[] = 'Return only the post text, ready to publish — authentic and engaging for LinkedIn.';

        return implode("\n", $lines);
    }
}
