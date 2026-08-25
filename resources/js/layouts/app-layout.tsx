import { Link, usePage } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';

import { AppSidebar } from '@/components/app-sidebar';
import { Separator } from '@/components/ui/separator';
import {
    SidebarInset,
    SidebarProvider,
    SidebarTrigger,
} from '@/components/ui/sidebar';
import type { BreadcrumbItem, SharedData } from '@/types';

interface AppLayoutProps extends PropsWithChildren {
    breadcrumbs?: BreadcrumbItem[];
}

export default function AppLayout({
    children,
    breadcrumbs = [],
}: AppLayoutProps) {
    const { sidebarOpen } = usePage<SharedData>().props;

    return (
        <SidebarProvider defaultOpen={sidebarOpen}>
            <AppSidebar />

            <SidebarInset>
                <header className="flex h-16 shrink-0 items-center gap-2 border-b px-4">
                    <SidebarTrigger className="-ml-1" />

                    {breadcrumbs.length > 0 && (
                        <>
                            <Separator
                                orientation="vertical"
                                className="mr-1 data-[orientation=vertical]:h-4"
                            />
                            <nav className="flex items-center gap-1.5 text-sm text-muted-foreground">
                                {breadcrumbs.map((item, index) => (
                                    <span
                                        key={item.href}
                                        className="flex items-center gap-1.5"
                                    >
                                        {index > 0 && (
                                            <span aria-hidden>/</span>
                                        )}
                                        <Link
                                            href={item.href}
                                            className="transition-colors hover:text-foreground"
                                        >
                                            {item.title}
                                        </Link>
                                    </span>
                                ))}
                            </nav>
                        </>
                    )}
                </header>

                <main className="flex flex-1 flex-col gap-4 p-4 md:p-6">
                    {children}
                </main>
            </SidebarInset>
        </SidebarProvider>
    );
}
