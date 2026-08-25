import { Link, usePage } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';

import Heading from '@/components/heading';
import { cn } from '@/lib/utils';
import { appearance } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import { edit as editPassword } from '@/routes/user-password';
import type { NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    { title: 'Profile', href: editProfile().url },
    { title: 'Password', href: editPassword().url },
    { title: 'Appearance', href: appearance().url },
];

export default function SettingsLayout({ children }: PropsWithChildren) {
    const currentPath = usePage().url;

    return (
        <div>
            <Heading
                title="Settings"
                description="Manage your profile and account settings"
            />

            <div className="flex flex-col gap-8 lg:flex-row">
                <aside className="w-full lg:w-48">
                    <nav className="flex flex-col gap-1">
                        {sidebarNavItems.map((item) => (
                            <Link
                                key={item.href}
                                href={item.href}
                                className={cn(
                                    'rounded-md px-3 py-2 text-sm font-medium transition-colors',
                                    currentPath.startsWith(item.href)
                                        ? 'bg-accent text-accent-foreground'
                                        : 'text-muted-foreground hover:text-foreground',
                                )}
                            >
                                {item.title}
                            </Link>
                        ))}
                    </nav>
                </aside>

                <div className="flex-1 md:max-w-2xl">
                    <section className="max-w-xl space-y-12">
                        {children}
                    </section>
                </div>
            </div>
        </div>
    );
}
