<?php

namespace App\Content;

use App\Ai\ProviderRequestException;
use App\Ai\TextGenerator;
use App\Models\Post;

class RewriteDraft
{
    public function __construct(private readonly TextGenerator $generator) {}

    /**
     * Rewrite the draft's body per a free-text instruction, in place.
     *
     * @throws ProviderRequestException when no text model responds
     */
    public function apply(Post $post, string $instruction): void
    {
        $post->update([
            'body' => $this->generator->generate($this->prompt($post, $instruction), maxTokens: 600),
        ]);
    }

    private function prompt(Post $post, string $instruction): string
    {
        return implode("\n", [
            'Revise the following LinkedIn post according to the instruction.',
            'Instruction: '.$instruction,
            'Current post:',
            $post->body,
            'Return only the revised post text, ready to publish.',
        ]);
    }
}
