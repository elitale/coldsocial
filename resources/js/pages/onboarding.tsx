import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';

import InputError from '@/components/input-error';
import { ChipGroup } from '@/components/onboarding/chip-group';
import { Field } from '@/components/onboarding/field';
import { OptionCards } from '@/components/onboarding/option-cards';
import { WhyNote } from '@/components/onboarding/why-note';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Progress } from '@/components/ui/progress';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { update } from '@/routes/onboarding';
import type { BreadcrumbItem } from '@/types';
import type { Persona, PersonaOptions } from '@/types/persona';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Persona', href: '/onboarding' },
];

const stepMeta = [
    {
        title: 'What do you want to achieve?',
        description: 'This sets the angle for everything we write for you.',
    },
    {
        title: 'About you',
        description: 'A little professional context and who you want to reach.',
    },
    { title: 'Your voice', description: 'How should your posts sound?' },
    {
        title: 'Interests & lines',
        description: 'Topics you love — and anything to steer clear of.',
    },
    {
        title: 'Your socials',
        description: 'Where you post, and the accounts we can reference.',
    },
    {
        title: 'Review & finish',
        description: "Anything else worth knowing, then you're set.",
    },
];

export default function Onboarding({
    persona,
    options,
}: {
    persona: Persona | null;
    options: PersonaOptions;
}) {
    const [step, setStep] = useState(0);

    const form = useForm({
        primary_goal: persona?.primary_goal ?? '',
        headline: persona?.headline ?? '',
        industry: persona?.industry ?? '',
        experience_level: persona?.experience_level ?? '',
        company: persona?.company ?? '',
        location: persona?.location ?? '',
        personality_archetype: persona?.personality_archetype ?? '',
        emoji_usage: persona?.emoji_usage ?? '',
        formality: persona?.formality ?? '',
        political_stance: persona?.political_stance ?? '',
        political_leaning: persona?.political_leaning ?? '',
        controversy_comfort: persona?.controversy_comfort ?? '',
        primary_platform: persona?.primary_platform ?? '',
        posting_frequency: persona?.posting_frequency ?? '',
        audience_note: persona?.audience_note ?? '',
        dislikes: persona?.dislikes ?? '',
        bio: persona?.bio ?? '',
        languages: persona?.languages ?? [],
        audiences: persona?.audiences ?? [],
        tones: persona?.tones ?? [],
        interests: persona?.interests ?? [],
        content_pillars: persona?.content_pillars ?? [],
        likes: persona?.likes ?? [],
        causes: persona?.causes ?? [],
        content_formats: persona?.content_formats ?? [],
        focus_platforms: persona?.focus_platforms ?? [],
        social_links: (persona?.social_links ?? {}) as Record<string, string>,
    });

    const { data, setData, processing, errors } = form;
    const socialErrors = errors as Record<string, string | undefined>;

    const total = stepMeta.length;
    const isLast = step === total - 1;

    const save = () => form.patch(update.url(), { preserveScroll: true });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Your persona" />

            <div className="mx-auto w-full max-w-3xl space-y-6">
                <div className="space-y-2">
                    <div className="flex items-center justify-between text-sm">
                        <span className="font-medium">
                            {stepMeta[step].title}
                        </span>
                        <span className="text-muted-foreground">
                            Step {step + 1} of {total}
                        </span>
                    </div>
                    <Progress value={((step + 1) / total) * 100} />
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{stepMeta[step].title}</CardTitle>
                        <CardDescription>
                            {stepMeta[step].description}
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-6">
                        {step === 0 && (
                            <OptionCards
                                name="primary_goal"
                                options={options.primary_goal}
                                value={data.primary_goal}
                                onChange={(v) => setData('primary_goal', v)}
                            />
                        )}

                        {step === 1 && (
                            <>
                                <Field
                                    label="Your headline / role"
                                    htmlFor="headline"
                                >
                                    <Input
                                        id="headline"
                                        value={data.headline}
                                        onChange={(e) =>
                                            setData('headline', e.target.value)
                                        }
                                        placeholder="e.g. Founder & CEO at Acme"
                                    />
                                </Field>
                                <Field label="Industry">
                                    <OptionCards
                                        name="industry"
                                        columns={3}
                                        options={options.industry}
                                        value={data.industry}
                                        onChange={(v) => setData('industry', v)}
                                    />
                                </Field>
                                <Field label="Experience level">
                                    <OptionCards
                                        name="experience_level"
                                        columns={3}
                                        options={options.experience_level}
                                        value={data.experience_level}
                                        onChange={(v) =>
                                            setData('experience_level', v)
                                        }
                                    />
                                </Field>
                                <div className="grid gap-4 sm:grid-cols-2">
                                    <Field
                                        label="Company / brand"
                                        htmlFor="company"
                                    >
                                        <Input
                                            id="company"
                                            value={data.company}
                                            onChange={(e) =>
                                                setData(
                                                    'company',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                    </Field>
                                    <Field label="Location" htmlFor="location">
                                        <Input
                                            id="location"
                                            value={data.location}
                                            onChange={(e) =>
                                                setData(
                                                    'location',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                    </Field>
                                </div>
                                <Field label="Languages you post in">
                                    <ChipGroup
                                        options={options.languages}
                                        values={data.languages}
                                        onChange={(v) =>
                                            setData('languages', v)
                                        }
                                    />
                                </Field>
                                <Field label="Who do you want to reach?">
                                    <ChipGroup
                                        options={options.audiences}
                                        values={data.audiences}
                                        onChange={(v) =>
                                            setData('audiences', v)
                                        }
                                    />
                                </Field>
                                <Field
                                    label="Describe your ideal audience"
                                    htmlFor="audience_note"
                                >
                                    <Textarea
                                        id="audience_note"
                                        value={data.audience_note}
                                        onChange={(e) =>
                                            setData(
                                                'audience_note',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="e.g. early-stage founders trying to grow on LinkedIn"
                                    />
                                </Field>
                            </>
                        )}

                        {step === 2 && (
                            <>
                                <Field label="Tone" hint="Pick as many as fit.">
                                    <ChipGroup
                                        options={options.tones}
                                        values={data.tones}
                                        onChange={(v) => setData('tones', v)}
                                    />
                                </Field>
                                <Field label="Personality">
                                    <OptionCards
                                        name="personality_archetype"
                                        columns={3}
                                        options={options.personality_archetype}
                                        value={data.personality_archetype}
                                        onChange={(v) =>
                                            setData('personality_archetype', v)
                                        }
                                    />
                                </Field>
                                <div className="grid gap-4 sm:grid-cols-2">
                                    <Field label="Emoji usage">
                                        <OptionCards
                                            name="emoji_usage"
                                            columns={3}
                                            options={options.emoji_usage}
                                            value={data.emoji_usage}
                                            onChange={(v) =>
                                                setData('emoji_usage', v)
                                            }
                                        />
                                    </Field>
                                    <Field label="Formality">
                                        <OptionCards
                                            name="formality"
                                            columns={3}
                                            options={options.formality}
                                            value={data.formality}
                                            onChange={(v) =>
                                                setData('formality', v)
                                            }
                                        />
                                    </Field>
                                </div>
                            </>
                        )}

                        {step === 3 && (
                            <>
                                <Field label="Interests & topics">
                                    <ChipGroup
                                        options={options.interests}
                                        values={data.interests}
                                        onChange={(v) =>
                                            setData('interests', v)
                                        }
                                    />
                                </Field>
                                <Field
                                    label="Top content pillars"
                                    hint="Pick up to 5 you want to be known for."
                                >
                                    <ChipGroup
                                        options={options.interests}
                                        values={data.content_pillars}
                                        onChange={(v) =>
                                            setData('content_pillars', v)
                                        }
                                        max={5}
                                    />
                                </Field>
                                <Field label="Content you like">
                                    <ChipGroup
                                        options={options.likes}
                                        values={data.likes}
                                        onChange={(v) => setData('likes', v)}
                                    />
                                </Field>
                                <Field
                                    label="Topics or words to avoid"
                                    htmlFor="dislikes"
                                >
                                    <Textarea
                                        id="dislikes"
                                        value={data.dislikes}
                                        onChange={(e) =>
                                            setData('dislikes', e.target.value)
                                        }
                                        placeholder="Anything we should never post about"
                                    />
                                </Field>
                                <Field label="Politics in your content">
                                    <OptionCards
                                        name="political_stance"
                                        columns={3}
                                        options={options.political_stance}
                                        value={data.political_stance}
                                        onChange={(v) =>
                                            setData('political_stance', v)
                                        }
                                    />
                                </Field>
                                <div className="grid gap-4 sm:grid-cols-2">
                                    <Field label="Political leaning (optional)">
                                        <OptionCards
                                            name="political_leaning"
                                            options={options.political_leaning}
                                            value={data.political_leaning}
                                            onChange={(v) =>
                                                setData('political_leaning', v)
                                            }
                                        />
                                    </Field>
                                    <Field label="Controversial topics">
                                        <OptionCards
                                            name="controversy_comfort"
                                            columns={3}
                                            options={
                                                options.controversy_comfort
                                            }
                                            value={data.controversy_comfort}
                                            onChange={(v) =>
                                                setData(
                                                    'controversy_comfort',
                                                    v,
                                                )
                                            }
                                        />
                                    </Field>
                                </div>
                                <Field label="Causes you care about">
                                    <ChipGroup
                                        options={options.causes}
                                        values={data.causes}
                                        onChange={(v) => setData('causes', v)}
                                    />
                                </Field>
                            </>
                        )}

                        {step === 4 && (
                            <>
                                <Field
                                    label="Your profile links"
                                    hint="Paste the URLs you have — leave the rest blank."
                                >
                                    <div className="grid gap-3 sm:grid-cols-2">
                                        {Object.entries(
                                            options.social_platforms,
                                        ).map(([key, label]) => (
                                            <div
                                                key={key}
                                                className="space-y-1.5"
                                            >
                                                <Label
                                                    htmlFor={`social-${key}`}
                                                    className="text-xs text-muted-foreground"
                                                >
                                                    {label}
                                                </Label>
                                                <Input
                                                    id={`social-${key}`}
                                                    type="url"
                                                    inputMode="url"
                                                    value={
                                                        data.social_links[
                                                            key
                                                        ] ?? ''
                                                    }
                                                    onChange={(e) =>
                                                        setData(
                                                            'social_links',
                                                            {
                                                                ...data.social_links,
                                                                [key]: e.target
                                                                    .value,
                                                            },
                                                        )
                                                    }
                                                    placeholder="https://…"
                                                />
                                                <InputError
                                                    message={
                                                        socialErrors[
                                                            `social_links.${key}`
                                                        ]
                                                    }
                                                />
                                            </div>
                                        ))}
                                    </div>
                                </Field>
                                <Field label="Primary platform">
                                    <OptionCards
                                        name="primary_platform"
                                        columns={3}
                                        options={options.platforms}
                                        value={data.primary_platform}
                                        onChange={(v) =>
                                            setData('primary_platform', v)
                                        }
                                    />
                                </Field>
                                <Field label="Platforms to focus on">
                                    <ChipGroup
                                        options={options.platforms}
                                        values={data.focus_platforms}
                                        onChange={(v) =>
                                            setData('focus_platforms', v)
                                        }
                                    />
                                </Field>
                                <Field label="Preferred formats">
                                    <ChipGroup
                                        options={options.content_formats}
                                        values={data.content_formats}
                                        onChange={(v) =>
                                            setData('content_formats', v)
                                        }
                                    />
                                </Field>
                                <Field label="Posting frequency">
                                    <OptionCards
                                        name="posting_frequency"
                                        columns={3}
                                        options={options.posting_frequency}
                                        value={data.posting_frequency}
                                        onChange={(v) =>
                                            setData('posting_frequency', v)
                                        }
                                    />
                                </Field>
                            </>
                        )}

                        {step === 5 && (
                            <Field
                                label="Anything else about you?"
                                htmlFor="bio"
                            >
                                <Textarea
                                    id="bio"
                                    value={data.bio}
                                    onChange={(e) =>
                                        setData('bio', e.target.value)
                                    }
                                    placeholder="A sentence or two we should know…"
                                    rows={4}
                                />
                            </Field>
                        )}
                    </CardContent>
                </Card>

                <div className="flex items-center justify-between">
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() => setStep((s) => Math.max(0, s - 1))}
                        disabled={step === 0}
                    >
                        Back
                    </Button>

                    <div className="flex gap-2">
                        {!isLast && (
                            <Button
                                type="button"
                                variant="ghost"
                                onClick={save}
                                disabled={processing}
                            >
                                Skip for now
                            </Button>
                        )}
                        {isLast ? (
                            <Button
                                type="button"
                                onClick={save}
                                disabled={processing}
                            >
                                {processing ? 'Saving…' : 'Finish'}
                            </Button>
                        ) : (
                            <Button
                                type="button"
                                onClick={() =>
                                    setStep((s) => Math.min(total - 1, s + 1))
                                }
                            >
                                Next
                            </Button>
                        )}
                    </div>
                </div>

                <WhyNote />
            </div>
        </AppLayout>
    );
}
