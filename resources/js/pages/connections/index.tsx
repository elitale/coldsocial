import { Head } from '@inertiajs/react';

import { PlatformIcon } from '@/components/platform-icon';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { index as connectionsIndex, redirect } from '@/routes/connections';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Connections', href: connectionsIndex().url },
];

type ConnectionStatus = 'connected' | 'available' | 'coming_soon';

interface PlatformCard {
    key: string;
    label: string;
    status: ConnectionStatus;
    accountName: string | null;
}

interface ConnectionsProps {
    platforms: PlatformCard[];
    flash: { success: string | null; error: string | null };
}

function ConnectionAction({ platform }: { platform: PlatformCard }) {
    if (platform.status === 'connected') {
        return <Badge>Connected</Badge>;
    }

    if (platform.status === 'available') {
        return (
            <Button asChild size="sm">
                <a href={redirect({ platform: platform.key }).url}>Connect</a>
            </Button>
        );
    }

    return (
        <Badge variant="secondary" className="font-normal">
            Coming soon
        </Badge>
    );
}

export default function ConnectionsIndex({
    platforms,
    flash,
}: ConnectionsProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Connections" />

            <div className="mx-auto flex w-full max-w-4xl flex-col gap-4 p-4">
                <div>
                    <h1 className="text-lg font-semibold">Connections</h1>
                    <p className="text-sm text-muted-foreground">
                        Connect your social accounts so coldsocial can publish
                        for you once you approve content.
                    </p>
                </div>

                {flash.success ? (
                    <div className="rounded-md border border-green-600/30 bg-green-600/10 px-4 py-3 text-sm text-green-700 dark:text-green-400">
                        {flash.success}
                    </div>
                ) : null}
                {flash.error ? (
                    <div className="rounded-md border border-destructive/30 bg-destructive/10 px-4 py-3 text-sm text-destructive">
                        {flash.error}
                    </div>
                ) : null}

                <div className="grid gap-4 sm:grid-cols-2">
                    {platforms.map((platform) => (
                        <Card
                            key={platform.key}
                            className={
                                platform.status === 'coming_soon'
                                    ? 'opacity-60'
                                    : undefined
                            }
                        >
                            <CardContent className="flex items-center gap-4 py-4">
                                <PlatformIcon
                                    platform={platform.key}
                                    className="size-8 shrink-0"
                                />
                                <div className="flex min-w-0 flex-1 flex-col">
                                    <span className="font-medium">
                                        {platform.label}
                                    </span>
                                    {platform.status === 'connected' &&
                                    platform.accountName ? (
                                        <span className="truncate text-sm text-muted-foreground">
                                            {platform.accountName}
                                        </span>
                                    ) : null}
                                </div>
                                <ConnectionAction platform={platform} />
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
