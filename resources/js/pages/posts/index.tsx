import { Form, Head, Link } from '@inertiajs/react';

import InputError from '@/components/input-error';
import { PostStatusBadge } from '@/components/post-status-badge';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { destroy, index, show, week } from '@/routes/posts';
import type { BreadcrumbItem } from '@/types';
import type { Post } from '@/types/post';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Posts', href: index().url }];

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

export default function PostsIndex({ posts }: { posts: Post[] }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Posts" />

            <div className="mx-auto flex w-full max-w-3xl flex-col gap-4 p-4">
                <div className="flex items-start justify-between gap-4">
                    <div>
                        <h1 className="text-lg font-semibold">Your drafts</h1>
                        <p className="text-sm text-muted-foreground">
                            Generated posts waiting for your review.
                        </p>
                    </div>
                    <Form
                        action={week.url()}
                        method="post"
                        options={{ preserveScroll: true }}
                    >
                        {({ processing, errors }) => (
                            <div className="flex flex-col items-end gap-1">
                                <Button type="submit" disabled={processing}>
                                    {processing
                                        ? 'Generating…'
                                        : 'Generate a week'}
                                </Button>
                                <InputError message={errors.generate} />
                            </div>
                        )}
                    </Form>
                </div>

                {posts.length === 0 ? (
                    <Card>
                        <CardContent className="py-10 text-center text-sm text-muted-foreground">
                            No drafts yet — generate one from an update.
                        </CardContent>
                    </Card>
                ) : (
                    posts.map((post) => (
                        <Card key={post.id}>
                            <CardContent className="flex items-start justify-between gap-4 py-4">
                                <Link
                                    href={show({ post: post.id }).url}
                                    className="flex min-w-0 flex-col gap-1"
                                >
                                    <span className="flex items-center gap-2">
                                        <PostStatusBadge status={post.status} />
                                        <Badge
                                            variant="secondary"
                                            className="uppercase"
                                        >
                                            {post.platform}
                                        </Badge>
                                        <span className="text-xs text-muted-foreground">
                                            {formatDate(post.created_at)}
                                        </span>
                                    </span>
                                    <span className="line-clamp-2 text-sm">
                                        {post.body}
                                    </span>
                                </Link>
                                <Form
                                    action={destroy.url({ post: post.id })}
                                    method="delete"
                                    options={{ preserveScroll: true }}
                                >
                                    {({ processing }) => (
                                        <Button
                                            type="submit"
                                            variant="ghost"
                                            size="sm"
                                            disabled={processing}
                                        >
                                            Delete
                                        </Button>
                                    )}
                                </Form>
                            </CardContent>
                        </Card>
                    ))
                )}
            </div>
        </AppLayout>
    );
}
