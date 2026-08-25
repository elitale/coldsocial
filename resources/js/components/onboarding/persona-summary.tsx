import { Sparkles } from 'lucide-react';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { PersonaOptions } from '@/types/persona';

export interface PersonaSummaryInput {
    primary_goal: string;
    industry: string;
    headline: string;
    tones: string[];
    personality_archetype: string;
    emoji_usage: string;
    formality: string;
    audiences: string[];
    primary_platform: string;
    posting_frequency: string;
    interests: string[];
    content_pillars: string[];
    political_stance: string;
    controversy_comfort: string;
}

const article = (word: string): string => (/^[aeiou]/i.test(word) ? 'an' : 'a');

const bareArchetype = (word: string): string => word.replace(/^The\s+/i, '');

function joinList(items: string[]): string {
    const list = items.filter(Boolean);

    if (list.length <= 1) {
        return list[0] ?? '';
    }

    if (list.length === 2) {
        return `${list[0]} and ${list[1]}`;
    }

    return `${list.slice(0, -1).join(', ')}, and ${list[list.length - 1]}`;
}

const EMOJI_PHRASE: Record<string, string> = {
    none: 'no emojis',
    minimal: 'a light touch of emojis',
    lots: 'plenty of emojis',
};

const STANCE_PHRASE: Record<string, string> = {
    apolitical: "you'll steer clear of politics",
    occasional: "you'll touch on politics now and then",
    openly: "you're open about where you stand politically",
};

const CONTROVERSY_CLAUSE: Record<string, string> = {
    avoid: 'and avoid controversy',
    cautious: 'and tread carefully around hot topics',
    open: "and you don't shy away from a hot take",
};

/**
 * Compose a friendly, second-person read of the persona purely from the user's
 * own selections. Deterministic — no model call — so it works the instant they fill fields.
 */
export function buildPersonaSummary(
    data: PersonaSummaryInput,
    options: PersonaOptions,
): string[] {
    const label = (group: string, value: string): string =>
        value ? (options[group]?.[value] ?? value) : '';
    const labelList = (group: string, values: string[]): string[] =>
        values.map((value) => options[group]?.[value] ?? value);

    const sentences: string[] = [];

    const goal = label('primary_goal', data.primary_goal);
    const industry = label('industry', data.industry);

    if (goal || industry || data.headline) {
        let sentence = goal
            ? `You're building your presence as ${article(goal)} ${goal}`
            : "You're building your presence";

        if (industry) {
            sentence += ` in ${industry}`;
        }

        if (data.headline) {
            sentence += ` — ${data.headline}`;
        }

        sentences.push(`${sentence}.`);
    }

    const tones = labelList('tones', data.tones);
    const personality = label(
        'personality_archetype',
        data.personality_archetype,
    );

    if (tones.length || personality || data.emoji_usage || data.formality) {
        let sentence = tones.length
            ? `Your voice comes across as ${joinList(tones)}`
            : 'Your voice is taking shape';

        if (personality) {
            const archetype = bareArchetype(personality);
            sentence += ` with ${article(archetype)} ${archetype} streak`;
        }

        if (EMOJI_PHRASE[data.emoji_usage]) {
            sentence += `, ${EMOJI_PHRASE[data.emoji_usage]}`;
        }

        if (data.formality) {
            sentence += `, and a ${label('formality', data.formality).toLowerCase()} register`;
        }

        sentences.push(`${sentence}.`);
    }

    const audiences = labelList('audiences', data.audiences);
    const platform = label('platforms', data.primary_platform);
    const frequency = label('posting_frequency', data.posting_frequency);

    if (audiences.length || platform || frequency) {
        let sentence = audiences.length
            ? `You're speaking to ${joinList(audiences)}`
            : "You're ready to post";

        if (platform) {
            sentence += ` on ${platform}`;
        }

        if (frequency) {
            sentence += `, ${frequency.toLowerCase()}`;
        }

        sentences.push(`${sentence}.`);
    }

    const pillars =
        data.content_pillars.length > 0 ? data.content_pillars : data.interests;
    const topics = labelList('interests', pillars).slice(0, 5);

    if (topics.length) {
        sentences.push(`Expect content around ${joinList(topics)}.`);
    }

    const stance = STANCE_PHRASE[data.political_stance];
    const controversy = CONTROVERSY_CLAUSE[data.controversy_comfort];

    if (stance || controversy) {
        const lead = stance ?? "you'll keep things measured";
        const clause = controversy ? ` ${controversy}` : '';
        sentences.push(
            `${lead.charAt(0).toUpperCase()}${lead.slice(1)}${clause}.`,
        );
    }

    return sentences;
}

export function PersonaSummary({
    data,
    options,
}: {
    data: PersonaSummaryInput;
    options: PersonaOptions;
}) {
    const sentences = buildPersonaSummary(data, options);

    return (
        <Card className="border-primary/30 bg-primary/5">
            <CardHeader className="flex items-center gap-2 space-y-0">
                <Sparkles className="size-5 text-primary" />
                <CardTitle className="text-base">
                    What we think about you
                </CardTitle>
            </CardHeader>
            <CardContent>
                {sentences.length > 0 ? (
                    <div className="space-y-2 text-sm text-muted-foreground">
                        {sentences.map((sentence, index) => (
                            <p key={index}>{sentence}</p>
                        ))}
                    </div>
                ) : (
                    <p className="text-sm text-muted-foreground">
                        Fill in a few answers above and we'll paint a picture of
                        the creator we'll write for.
                    </p>
                )}
            </CardContent>
        </Card>
    );
}
