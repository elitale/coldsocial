import { Head } from '@inertiajs/react';

import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
];

export default function Dashboard() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />

            <div className="flex flex-col gap-4">
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    {[0, 1, 2].map((i) => (
                        <div
                            key={i}
                            className="aspect-video rounded-xl border border-border bg-card"
                        />
                    ))}
                </div>
                <div className="min-h-64 rounded-xl border border-border bg-card p-6">
                    <h1 className="text-lg font-semibold">
                        Welcome to coldsocial
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Your social presence, automated. Content generation,
                        scheduling, and performance insights arrive in upcoming
                        features.
                    </p>
                </div>
            </div>
        </AppLayout>
    );
}
