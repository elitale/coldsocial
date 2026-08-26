import { Form, Head } from '@inertiajs/react';

import InputError from '@/components/input-error';
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
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { destroy, index, store } from '@/routes/updates';
import type { BreadcrumbItem } from '@/types';
import type { Update } from '@/types/update';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Updates', href: index().url }];

export default function Updates({ updates }: { updates: Update[] }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Updates" />

            <div className="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4">
                <Card>
                    <CardHeader>
                        <CardTitle>Capture an update</CardTitle>
                        <CardDescription>
                            Jot down what&apos;s new — a launch, a milestone, a
                            hot take. We&apos;ll turn it into posts later.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Form
                            action={store.url()}
                            method="post"
                            options={{ preserveScroll: true }}
                            resetOnSuccess
                            className="flex flex-col gap-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="body">
                                            What&apos;s new?
                                        </Label>
                                        <Textarea
                                            id="body"
                                            name="body"
                                            required
                                            rows={4}
                                            placeholder="We just shipped…"
                                        />
                                        <InputError message={errors.body} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="source_url">
                                            Source link (optional)
                                        </Label>
                                        <Input
                                            id="source_url"
                                            name="source_url"
                                            type="url"
                                            placeholder="https://…"
                                        />
                                        <InputError
                                            message={errors.source_url}
                                        />
                                    </div>

                                    <div>
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            Capture
                                        </Button>
                                    </div>
                                </>
                            )}
                        </Form>
                    </CardContent>
                </Card>

                <div className="flex flex-col gap-3">
                    {updates.length === 0 ? (
                        <Card>
                            <CardContent className="py-10 text-center text-sm text-muted-foreground">
                                No updates yet — jot down what&apos;s new above.
                            </CardContent>
                        </Card>
                    ) : (
                        updates.map((update) => (
                            <Card key={update.id}>
                                <CardContent className="flex items-start justify-between gap-4 py-4">
                                    <div className="flex flex-col gap-1">
                                        <p className="text-sm whitespace-pre-wrap">
                                            {update.body}
                                        </p>
                                        {update.source_url && (
                                            <a
                                                href={update.source_url}
                                                target="_blank"
                                                rel="noreferrer"
                                                className="text-xs text-muted-foreground underline underline-offset-4"
                                            >
                                                {update.source_url}
                                            </a>
                                        )}
                                    </div>
                                    <Form
                                        action={destroy.url({
                                            update: update.id,
                                        })}
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
            </div>
        </AppLayout>
    );
}
