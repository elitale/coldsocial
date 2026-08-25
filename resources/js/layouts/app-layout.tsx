import { Link } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';

import AppHeader from '@/components/app-header';
import type { BreadcrumbItem } from '@/types';

interface AppLayoutProps extends PropsWithChildren {
    breadcrumbs?: BreadcrumbItem[];
}

export default function AppLayout({
    children,
    breadcrumbs = [],
}: AppLayoutProps) {
    return (
        <div className="flex min-h-svh flex-col bg-background">
            <AppHeader />

            <main className="mx-auto w-full max-w-7xl flex-1 px-4 py-6 md:px-6">
                {breadcrumbs.length > 0 && (
                    <nav className="mb-6 flex items-center gap-1.5 text-sm text-muted-foreground">
                        {breadcrumbs.map((item, index) => (
                            <span
                                key={item.href}
                                className="flex items-center gap-1.5"
                            >
                                {index > 0 && <span aria-hidden>/</span>}
                                <Link
                                    href={item.href}
                                    className="transition-colors hover:text-foreground"
                                >
                                    {item.title}
                                </Link>
                            </span>
                        ))}
                    </nav>
                )}

                {children}
            </main>
        </div>
    );
}
