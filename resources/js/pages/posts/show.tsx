import { Form, Head, Link } from '@inertiajs/react';

import InputError from '@/components/input-error';
import { PostStatusBadge } from '@/components/post-status-badge';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import {
    approve,
    edit as editPost,
    regenerate,
    schedule,
    unapprove,
    unschedule,
} from '@/routes/posts';
import { index as updatesIndex } from '@/routes/updates';
import type { BreadcrumbItem } from '@/types';
import type { Post } from '@/types/post';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Updates', href: updatesIndex().url },
    { title: 'Draft', href: '#' },
];

function formatDateTime(iso: string, timezone: string): string {
    return new Date(iso).toLocaleString(undefined, {
        timeZone: timezone,
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}

export default function ShowPost({
    post,
    timezone,
    scheduledInput,
}: {
    post: Post;
    timezone: string;
    scheduledInput: string | null;
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Draft post" />

            <div className="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle>Your LinkedIn draft</CardTitle>
                        <div className="flex items-center gap-2">
                            <PostStatusBadge status={post.status} />
                            <Badge variant="secondary" className="uppercase">
                                {post.platform}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-4">
                        <p className="text-sm whitespace-pre-wrap">
                            {post.body}
                        </p>

                        {post.source_update && (
                            <p className="text-xs text-muted-foreground">
                                Generated from a captured update
                                {post.source_update.source_url && (
                                    <>
                                        {' · '}
                                        <a
                                            href={post.source_update.source_url}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="underline underline-offset-4"
                                        >
                                            source
                                        </a>
                                    </>
                                )}
                            </p>
                        )}

                        <div className="flex flex-wrap gap-2">
                            {post.status === 'approved' ? (
                                <Form
                                    action={unapprove.url({ post: post.id })}
                                    method="post"
                                    options={{ preserveScroll: true }}
                                >
                                    {({ processing }) => (
                                        <Button
                                            type="submit"
                                            variant="outline"
                                            size="sm"
                                            disabled={processing}
                                        >
                                            Unapprove
                                        </Button>
                                    )}
                                </Form>
                            ) : (
                                <Form
                                    action={approve.url({ post: post.id })}
                                    method="post"
                                    options={{ preserveScroll: true }}
                                >
                                    {({ processing }) => (
                                        <Button
                                            type="submit"
                                            size="sm"
                                            disabled={processing}
                                        >
                                            Approve
                                        </Button>
                                    )}
                                </Form>
                            )}
                            <Button asChild variant="outline" size="sm">
                                <Link href={editPost({ post: post.id }).url}>
                                    Edit
                                </Link>
                            </Button>
                            <Button asChild variant="ghost" size="sm">
                                <Link href={updatesIndex().url}>
                                    Back to updates
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                {(post.status === 'approved' ||
                    post.status === 'scheduled') && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Schedule</CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-4">
                            {post.status === 'scheduled' &&
                                post.scheduled_at && (
                                    <p className="text-sm">
                                        Scheduled for{' '}
                                        <span className="font-medium">
                                            {formatDateTime(
                                                post.scheduled_at,
                                                timezone,
                                            )}
                                        </span>{' '}
                                        <span className="text-muted-foreground">
                                            ({timezone})
                                        </span>
                                    </p>
                                )}
                            <Form
                                action={schedule.url({ post: post.id })}
                                method="post"
                                options={{ preserveScroll: true }}
                                className="flex flex-col gap-3"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <Label htmlFor="scheduled_at">
                                                {post.status === 'scheduled'
                                                    ? 'Reschedule for'
                                                    : 'Pick a date & time'}
                                            </Label>
                                            <Input
                                                id="scheduled_at"
                                                name="scheduled_at"
                                                type="datetime-local"
                                                defaultValue={
                                                    scheduledInput ?? ''
                                                }
                                                required
                                            />
                                            <InputError
                                                message={errors.scheduled_at}
                                            />
                                        </div>
                                        <div>
                                            <Button
                                                type="submit"
                                                disabled={processing}
                                            >
                                                {post.status === 'scheduled'
                                                    ? 'Reschedule'
                                                    : 'Schedule'}
                                            </Button>
                                        </div>
                                    </>
                                )}
                            </Form>
                            {post.status === 'scheduled' && (
                                <Form
                                    action={unschedule.url({ post: post.id })}
                                    method="post"
                                    options={{ preserveScroll: true }}
                                >
                                    {({ processing }) => (
                                        <Button
                                            type="submit"
                                            variant="outline"
                                            size="sm"
                                            disabled={processing}
                                        >
                                            Unschedule
                                        </Button>
                                    )}
                                </Form>
                            )}
                        </CardContent>
                    </Card>
                )}

                <Card>
                    <CardHeader>
                        <CardTitle>Tweak this draft</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Form
                            action={regenerate.url({ post: post.id })}
                            method="post"
                            options={{ preserveScroll: true }}
                            resetOnSuccess
                            className="flex flex-col gap-3"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="instruction">
                                            How should we change it?
                                        </Label>
                                        <Input
                                            id="instruction"
                                            name="instruction"
                                            required
                                            placeholder="Make it shorter and add a call to action"
                                        />
                                        <InputError
                                            message={errors.instruction}
                                        />
                                        <InputError
                                            message={errors.regenerate}
                                        />
                                    </div>
                                    <div>
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            {processing
                                                ? 'Rewriting…'
                                                : 'Regenerate'}
                                        </Button>
                                    </div>
                                </>
                            )}
                        </Form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
