import { Head, Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { index as calendarIndex } from '@/routes/calendar';
import { show as showPost } from '@/routes/posts';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Calendar', href: calendarIndex().url },
];

const WEEKDAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

interface CalendarPost {
    id: number;
    platform: string;
    status: 'draft' | 'approved' | 'scheduled';
    time: string;
    excerpt: string;
}

interface CalendarProps {
    month: string;
    monthLabel: string;
    timezone: string;
    firstWeekday: number;
    daysInMonth: number;
    today: string;
    prevMonth: string;
    nextMonth: string;
    postsByDay: Record<string, CalendarPost[]>;
}

export default function CalendarIndex({
    month,
    monthLabel,
    timezone,
    firstWeekday,
    daysInMonth,
    today,
    prevMonth,
    nextMonth,
    postsByDay,
}: CalendarProps) {
    const cells: (number | null)[] = [
        ...Array.from({ length: firstWeekday }, () => null),
        ...Array.from({ length: daysInMonth }, (_, i) => i + 1),
    ];

    while (cells.length % 7 !== 0) {
        cells.push(null);
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Calendar" />

            <div className="mx-auto flex w-full max-w-5xl flex-col gap-4 p-4">
                <div className="flex items-center justify-between gap-4">
                    <div>
                        <h1 className="text-lg font-semibold">{monthLabel}</h1>
                        <p className="text-sm text-muted-foreground">
                            Scheduled posts, shown in {timezone}.
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        <Button variant="outline" size="icon" asChild>
                            <Link
                                href={
                                    calendarIndex({
                                        query: { month: prevMonth },
                                    }).url
                                }
                                aria-label="Previous month"
                            >
                                <ChevronLeft className="size-4" />
                            </Link>
                        </Button>
                        <Button variant="outline" size="sm" asChild>
                            <Link href={calendarIndex().url}>Today</Link>
                        </Button>
                        <Button variant="outline" size="icon" asChild>
                            <Link
                                href={
                                    calendarIndex({
                                        query: { month: nextMonth },
                                    }).url
                                }
                                aria-label="Next month"
                            >
                                <ChevronRight className="size-4" />
                            </Link>
                        </Button>
                    </div>
                </div>

                <Card>
                    <CardContent className="p-0">
                        <div className="grid grid-cols-7 border-b text-center text-xs font-medium text-muted-foreground">
                            {WEEKDAYS.map((day) => (
                                <div key={day} className="py-2">
                                    {day}
                                </div>
                            ))}
                        </div>
                        <div className="grid grid-cols-7">
                            {cells.map((day, index) => {
                                if (day === null) {
                                    return (
                                        <div
                                            key={`empty-${index}`}
                                            className="min-h-24 border-r border-b bg-muted/30 [&:nth-child(7n)]:border-r-0"
                                        />
                                    );
                                }

                                const dateKey = `${month}-${String(day).padStart(2, '0')}`;
                                const dayPosts = postsByDay[dateKey] ?? [];
                                const isToday = dateKey === today;

                                return (
                                    <div
                                        key={dateKey}
                                        className="flex min-h-24 flex-col gap-1 border-r border-b p-1.5 [&:nth-child(7n)]:border-r-0"
                                    >
                                        <span
                                            className={
                                                isToday
                                                    ? 'flex size-6 items-center justify-center self-start rounded-full bg-primary text-xs font-semibold text-primary-foreground'
                                                    : 'flex size-6 items-center justify-center self-start text-xs font-medium text-muted-foreground'
                                            }
                                        >
                                            {day}
                                        </span>
                                        {dayPosts.map((post) => (
                                            <Link
                                                key={post.id}
                                                href={
                                                    showPost({ post: post.id })
                                                        .url
                                                }
                                                title={post.excerpt}
                                                className="flex flex-col rounded-md border bg-card px-1.5 py-1 text-left text-xs transition-colors hover:bg-accent"
                                            >
                                                <span className="font-medium">
                                                    {post.time}{' '}
                                                    <span className="text-muted-foreground uppercase">
                                                        {post.platform}
                                                    </span>
                                                </span>
                                                <span className="truncate text-muted-foreground">
                                                    {post.excerpt}
                                                </span>
                                            </Link>
                                        ))}
                                    </div>
                                );
                            })}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
