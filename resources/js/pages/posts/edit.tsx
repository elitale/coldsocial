import { Form, Head, Link } from '@inertiajs/react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { index as postsIndex, show, update } from '@/routes/posts';
import type { BreadcrumbItem } from '@/types';
import type { Post } from '@/types/post';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Posts', href: postsIndex().url },
    { title: 'Edit draft', href: '#' },
];

export default function EditPost({ post }: { post: Post }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit draft" />

            <div className="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4">
                <Card>
                    <CardHeader>
                        <CardTitle>Edit your draft</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Form
                            action={update.url({ post: post.id })}
                            method="patch"
                            options={{ preserveScroll: true }}
                            className="flex flex-col gap-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Textarea
                                            name="body"
                                            defaultValue={post.body}
                                            required
                                            rows={10}
                                            aria-label="Post body"
                                        />
                                        <InputError message={errors.body} />
                                    </div>

                                    <div className="flex gap-2">
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            Save
                                        </Button>
                                        <Button asChild variant="outline">
                                            <Link
                                                href={
                                                    show({ post: post.id }).url
                                                }
                                            >
                                                Cancel
                                            </Link>
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
