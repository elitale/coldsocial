import { Link, usePage } from '@inertiajs/react';
import {
    CalendarDays,
    FileText,
    LayoutGrid,
    Newspaper,
    Plug,
    UserRound,
} from 'lucide-react';

import AppLogoIcon from '@/components/app-logo-icon';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as calendarIndex } from '@/routes/calendar';
import { index as connectionsIndex } from '@/routes/connections';
import { edit as editPersona } from '@/routes/onboarding';
import { index as postsIndex } from '@/routes/posts';
import { index as updatesIndex } from '@/routes/updates';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    { title: 'Dashboard', href: dashboard().url, icon: LayoutGrid },
    { title: 'Persona', href: editPersona().url, icon: UserRound },
    { title: 'Updates', href: updatesIndex().url, icon: Newspaper },
    { title: 'Posts', href: postsIndex().url, icon: FileText },
    { title: 'Calendar', href: calendarIndex().url, icon: CalendarDays },
    { title: 'Connections', href: connectionsIndex().url, icon: Plug },
];

export function AppSidebar() {
    const page = usePage();

    return (
        <Sidebar collapsible="icon">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()}>
                                <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-primary text-primary-foreground">
                                    <AppLogoIcon className="size-5" />
                                </div>
                                <span className="text-base font-semibold">
                                    coldsocial
                                </span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <SidebarGroup>
                    <SidebarGroupLabel>Platform</SidebarGroupLabel>
                    <SidebarMenu>
                        {mainNavItems.map((item) => {
                            const Icon = item.icon;

                            return (
                                <SidebarMenuItem key={item.title}>
                                    <SidebarMenuButton
                                        asChild
                                        isActive={page.url.startsWith(
                                            item.href,
                                        )}
                                        tooltip={item.title}
                                    >
                                        <Link href={item.href}>
                                            {Icon && <Icon />}
                                            <span>{item.title}</span>
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            );
                        })}
                    </SidebarMenu>
                </SidebarGroup>
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
