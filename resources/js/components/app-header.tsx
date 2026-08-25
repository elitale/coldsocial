import { Link, usePage } from '@inertiajs/react';
import { LogOut, Settings } from 'lucide-react';

import AppLogo from '@/components/app-logo';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/user-info';
import { cn } from '@/lib/utils';
import { dashboard, logout } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import type { NavItem, SharedData } from '@/types';

const mainNavItems: NavItem[] = [{ title: 'Dashboard', href: dashboard().url }];

export default function AppHeader() {
    const page = usePage<SharedData>();
    const { auth } = page.props;

    return (
        <header className="border-b border-border/80">
            <div className="mx-auto flex h-16 w-full max-w-7xl items-center justify-between gap-4 px-4 md:px-6">
                <div className="flex items-center gap-6">
                    <Link
                        href={dashboard()}
                        className="flex items-center gap-2"
                    >
                        <AppLogo />
                    </Link>

                    <nav className="hidden items-center gap-1 lg:flex">
                        {mainNavItems.map((item) => (
                            <Link
                                key={item.href}
                                href={item.href}
                                className={cn(
                                    'rounded-md px-3 py-2 text-sm font-medium transition-colors',
                                    page.url.startsWith(item.href)
                                        ? 'bg-accent text-accent-foreground'
                                        : 'text-muted-foreground hover:text-foreground',
                                )}
                            >
                                {item.title}
                            </Link>
                        ))}
                    </nav>
                </div>

                <DropdownMenu>
                    <DropdownMenuTrigger className="flex items-center gap-2 rounded-full p-1 pr-3 outline-none hover:bg-accent focus-visible:ring-2 focus-visible:ring-ring">
                        <UserInfo user={auth.user} />
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" className="w-56">
                        <DropdownMenuLabel className="p-0 font-normal">
                            <div className="flex items-center gap-2 px-1 py-1.5">
                                <UserInfo user={auth.user} showEmail />
                            </div>
                        </DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem asChild>
                            <Link href={editProfile()} className="w-full">
                                <Settings className="mr-2" />
                                Settings
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem asChild>
                            <Link
                                href={logout()}
                                as="button"
                                className="w-full"
                            >
                                <LogOut className="mr-2" />
                                Log out
                            </Link>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </header>
    );
}
