import { Head, Link } from '@inertiajs/react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { edit as editPost } from '@/routes/posts';
import { index as updatesIndex } from '@/routes/updates';
import type { BreadcrumbItem } from '@/types';
import type { Post } from '@/types/post';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Updates', href: updatesIndex().url },
    { title: 'Draft', href: '#' },
];

export default function ShowPost({ post }: { post: Post }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Draft post" />

            <div className="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle>Your LinkedIn draft</CardTitle>
                        <Badge variant="secondary" className="uppercase">
                            {post.platform}
                        </Badge>
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

                        <div className="flex gap-2">
                            <Button asChild size="sm">
                                <Link href={editPost({ post: post.id }).url}>
                                    Edit
                                </Link>
                            </Button>
                            <Button asChild variant="outline" size="sm">
                                <Link href={updatesIndex().url}>
                                    Back to updates
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
