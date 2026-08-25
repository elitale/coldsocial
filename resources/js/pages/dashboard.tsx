import { Head } from '@inertiajs/react';

import { ChartArea } from '@/components/dashboard/chart-area';
import { RecentActivity } from '@/components/dashboard/recent-activity';
import { SectionCards } from '@/components/dashboard/section-cards';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
];

export default function Dashboard() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />

            <div className="flex flex-col gap-4 md:gap-6">
                <SectionCards />
                <ChartArea />
                <RecentActivity />
            </div>
        </AppLayout>
    );
}
