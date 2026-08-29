import { Head, router } from '@inertiajs/react';
import * as React from 'react';

import InputError from '@/components/input-error';
import { PlatformIcon } from '@/components/platform-icon';
import { LinkedInPreview } from '@/components/previews/linkedin-preview';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import {
    create as studioCreate,
    generate as studioGenerate,
    store as studioStore,
} from '@/routes/studio';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Studio', href: studioCreate().url },
];

const HASHTAG_PATTERN = /#[\p{L}0-9_]+/gu;

interface StudioSpec {
    charLimit: number;
    hashtagMin: number;
    hashtagMax: number;
}

interface StudioProps {
    platformLabel: string;
    spec: StudioSpec;
    author: { name: string; headline: string | null };
    timezone: string;
    generated: string | null;
}

export default function Studio({
    platformLabel,
    spec,
    author,
    timezone,
}: StudioProps) {
    const [body, setBody] = React.useState('');
    const [prompt, setPrompt] = React.useState('');
    const [scheduledAt, setScheduledAt] = React.useState('');
    const [busy, setBusy] = React.useState(false);
    const [errors, setErrors] = React.useState<Record<string, string>>({});

    const hashtagCount = (body.match(HASHTAG_PATTERN) ?? []).length;
    const overLimit = body.length > spec.charLimit;
    const empty = body.trim() === '';

    function generate() {
        setBusy(true);
        router.post(
            studioGenerate.url(),
            { prompt },
            {
                preserveScroll: true,
                onSuccess: (page) => {
                    const caption = (
                        page.props as { generated?: string | null }
                    ).generated;

                    if (caption) {
                        setBody(caption);
                    }

                    setErrors({});
                },
                onError: (formErrors) => setErrors(formErrors),
                onFinish: () => setBusy(false),
            },
        );
    }

    function save(withSchedule: boolean) {
        setBusy(true);
        router.post(
            studioStore.url(),
            { body, scheduled_at: withSchedule ? scheduledAt : '' },
            {
                onError: (formErrors) => setErrors(formErrors),
                onFinish: () => setBusy(false),
            },
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Studio" />

            <div className="mx-auto flex w-full max-w-6xl flex-col gap-4 p-4">
                <div>
                    <h1 className="text-lg font-semibold">Studio</h1>
                    <p className="text-sm text-muted-foreground">
                        Compose a post, preview it exactly as it'll appear, then
                        save a draft or schedule it.
                    </p>
                </div>

                <div className="flex flex-wrap items-center gap-2">
                    <span className="flex items-center gap-1.5 rounded-md border px-2.5 py-1 text-sm font-medium">
                        <PlatformIcon platform="linkedin" className="size-4" />
                        {platformLabel}
                    </span>
                    <span className="text-muted-foreground">·</span>
                    <Badge>Text</Badge>
                    <Badge variant="outline" className="text-muted-foreground">
                        Image — soon
                    </Badge>
                    <Badge variant="outline" className="text-muted-foreground">
                        Carousel — soon
                    </Badge>
                    <Badge variant="outline" className="text-muted-foreground">
                        Video — soon
                    </Badge>
                </div>

                <div className="grid gap-4 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Compose</CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="prompt">Topic (optional)</Label>
                                <div className="flex gap-2">
                                    <Input
                                        id="prompt"
                                        value={prompt}
                                        onChange={(e) =>
                                            setPrompt(e.target.value)
                                        }
                                        placeholder="What's this post about?"
                                    />
                                    <Button
                                        type="button"
                                        variant="secondary"
                                        onClick={generate}
                                        disabled={busy}
                                    >
                                        {busy ? 'Working…' : 'Generate'}
                                    </Button>
                                </div>
                                <InputError message={errors.prompt} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="body">Post</Label>
                                <Textarea
                                    id="body"
                                    value={body}
                                    onChange={(e) => setBody(e.target.value)}
                                    rows={10}
                                    placeholder="Write your post, or hit Generate…"
                                />
                                <div className="flex items-center justify-between text-xs text-muted-foreground">
                                    <span
                                        className={
                                            overLimit
                                                ? 'text-destructive'
                                                : undefined
                                        }
                                    >
                                        {body.length} / {spec.charLimit}
                                    </span>
                                    <span
                                        className={
                                            hashtagCount > spec.hashtagMax
                                                ? 'text-destructive'
                                                : undefined
                                        }
                                    >
                                        {hashtagCount} hashtags ·{' '}
                                        {spec.hashtagMin}–{spec.hashtagMax}{' '}
                                        recommended
                                    </span>
                                </div>
                                <InputError message={errors.body} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="scheduled_at">
                                    Schedule for (optional)
                                </Label>
                                <Input
                                    id="scheduled_at"
                                    type="datetime-local"
                                    value={scheduledAt}
                                    onChange={(e) =>
                                        setScheduledAt(e.target.value)
                                    }
                                />
                                <p className="text-xs text-muted-foreground">
                                    Times are in {timezone}.
                                </p>
                                <InputError message={errors.scheduled_at} />
                            </div>

                            <div className="flex flex-wrap gap-2">
                                <Button
                                    onClick={() => save(false)}
                                    disabled={busy || empty}
                                >
                                    Save draft
                                </Button>
                                <Button
                                    variant="secondary"
                                    onClick={() => save(true)}
                                    disabled={
                                        busy || empty || scheduledAt === ''
                                    }
                                >
                                    Schedule
                                </Button>
                                <Button
                                    variant="outline"
                                    disabled
                                    title="Coming with publishing (#19)"
                                >
                                    Post now
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <div className="flex flex-col gap-2">
                        <span className="text-sm font-medium text-muted-foreground">
                            Preview
                        </span>
                        <LinkedInPreview
                            authorName={author.name}
                            headline={author.headline}
                            body={body}
                        />
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
