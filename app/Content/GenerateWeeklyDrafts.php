<?php

namespace App\Content;

use App\Ai\ProviderRequestException;
use App\Ai\TextGenerator;
use App\Models\Persona;
use App\Models\Post;
use App\Models\Update;
use App\Models\User;
use Illuminate\Support\Collection;

class GenerateWeeklyDrafts
{
    /**
     * Distinct angles so a week's posts don't all sound the same.
     *
     * @var list<string>
     */
    private const ANGLES = [
        'a practical how-to or tip the audience can use today',
        'a short personal story or lesson learned',
        'a bold, professional opinion or contrarian take',
        'an engaging question or poll that invites discussion',
        'a behind-the-scenes look at what you are working on',
    ];

    /**
     * How many recent updates to feed in as shared context.
     */
    private const CONTEXT_UPDATES = 5;

    public function __construct(private readonly TextGenerator $generator) {}

    /**
     * Generate a week of LinkedIn drafts (one per angle) for the user and store them.
     *
     * @return Collection<int, Post>
     *
     * @throws ProviderRequestException when no text model responds
     */
    public function forUser(User $user): Collection
    {
        $persona = $user->persona;
        /** @var Collection<int, Update> $updates */
        $updates = $user->updates()->latest()->limit(self::CONTEXT_UPDATES)->get();

        // Generate every body first so a mid-batch failure leaves nothing half-created.
        $bodies = array_map(
            fn (string $angle): string => $this->generator->generate($this->prompt($persona, $updates, $angle), maxTokens: 600),
            self::ANGLES,
        );

        return collect($bodies)->map(fn (string $body): Post => $user->posts()->create([
            'platform' => 'linkedin',
            'body' => $body,
        ]));
    }

    /**
     * @param  Collection<int, Update>  $updates
     */
    private function prompt(?Persona $persona, Collection $updates, string $angle): string
    {
        $lines = ['Write a single LinkedIn post.'];

        foreach (PersonaVoice::hints($persona) as $hint) {
            $lines[] = $hint;
        }

        if ($updates->isNotEmpty()) {
            $lines[] = 'Recent updates to draw from:';

            foreach ($updates as $update) {
                $lines[] = '- '.$update->body;
            }
        }

        $lines[] = 'Angle for this post: '.$angle;
        $lines[] = 'Return only the post text, ready to publish — authentic and engaging for LinkedIn.';

        return implode("\n", $lines);
    }
}
