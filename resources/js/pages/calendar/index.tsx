import { Head, Link, router } from '@inertiajs/react';
import * as React from 'react';

import { Button } from '@/components/ui/button';
import { Calendar, CalendarDayButton } from '@/components/ui/calendar';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { index as calendarIndex } from '@/routes/calendar';
import { show as showPost } from '@/routes/posts';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Calendar', href: calendarIndex().url },
];

interface CalendarPost {
    id: number;
    platform: string;
    status: 'draft' | 'approved' | 'scheduled';
    time: string;
    excerpt: string;
}

interface CalendarProps {
    month: string;
    timezone: string;
    today: string;
    postsByDay: Record<string, CalendarPost[]>;
}

function pad(value: number): string {
    return String(value).padStart(2, '0');
}

function parseMonth(month: string): Date {
    const [year, monthNumber] = month.split('-').map(Number);

    return new Date(year, monthNumber - 1, 1);
}

function parseDay(day: string): Date {
    const [year, monthNumber, dayNumber] = day.split('-').map(Number);

    return new Date(year, monthNumber - 1, dayNumber);
}

function toDayKey(date: Date): string {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
}

function toMonthKey(date: Date): string {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}`;
}

function isSameMonth(a: Date, b: Date): boolean {
    return a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth();
}

function formatDayLabel(date: Date): string {
    return date.toLocaleDateString(undefined, {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
    });
}

export default function CalendarIndex({
    month,
    timezone,
    today,
    postsByDay,
}: CalendarProps) {
    const monthStart = parseMonth(month);
    const todayDate = parseDay(today);
    const rangeStart = new Date(todayDate.getFullYear() - 2, 0, 1);
    const rangeEnd = new Date(todayDate.getFullYear() + 5, 11, 1);

    // Keep an explicit pick, but fall back to a sensible default whenever the
    // shown month changes (the old pick no longer belongs to it).
    const [pickedDay, setPickedDay] = React.useState<Date | null>(null);

    const defaultDay = isSameMonth(todayDate, monthStart)
        ? todayDate
        : monthStart;
    const selected =
        pickedDay && isSameMonth(pickedDay, monthStart)
            ? pickedDay
            : defaultDay;

    const scheduledDates = Object.keys(postsByDay).map(parseDay);
    const selectedPosts = postsByDay[toDayKey(selected)] ?? [];

    function goToMonth(next: Date): void {
        router.get(
            calendarIndex({ query: { month: toMonthKey(next) } }).url,
            {},
            { preserveScroll: true, preserveState: false },
        );
    }

    function handleSelect(date: Date | undefined): void {
        if (date) {
            setPickedDay(date);
        }
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Calendar" />

            <div className="mx-auto flex w-full max-w-4xl flex-col gap-4 p-4">
                <div className="flex items-center justify-between gap-4">
                    <div>
                        <h1 className="text-lg font-semibold">Calendar</h1>
                        <p className="text-sm text-muted-foreground">
                            Scheduled posts, shown in {timezone}.
                        </p>
                    </div>
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => goToMonth(new Date())}
                    >
                        Today
                    </Button>
                </div>

                <div className="flex flex-col gap-4 md:flex-row md:items-start">
                    <Calendar
                        mode="single"
                        month={monthStart}
                        onMonthChange={goToMonth}
                        selected={selected}
                        onSelect={handleSelect}
                        today={todayDate}
                        captionLayout="dropdown"
                        startMonth={rangeStart}
                        endMonth={rangeEnd}
                        showOutsideDays={false}
                        modifiers={{ scheduled: scheduledDates }}
                        className="rounded-lg border shadow-sm"
                        components={{
                            DayButton: (
                                dayProps: React.ComponentProps<
                                    typeof CalendarDayButton
                                >,
                            ) => {
                                const count =
                                    postsByDay[toDayKey(dayProps.day.date)]
                                        ?.length ?? 0;

                                return (
                                    <CalendarDayButton {...dayProps}>
                                        {dayProps.children}
                                        {count > 0 ? (
                                            <span className="size-1 rounded-full bg-current" />
                                        ) : null}
                                    </CalendarDayButton>
                                );
                            },
                        }}
                    />

                    <Card className="flex-1">
                        <CardHeader>
                            <CardTitle className="text-base">
                                {formatDayLabel(selected)}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-2">
                            {selectedPosts.length === 0 ? (
                                <p className="text-sm text-muted-foreground">
                                    No posts scheduled for this day.
                                </p>
                            ) : (
                                selectedPosts.map((post) => (
                                    <Link
                                        key={post.id}
                                        href={showPost({ post: post.id }).url}
                                        className="flex flex-col gap-1 rounded-md border p-3 text-sm transition-colors hover:bg-accent"
                                    >
                                        <span className="flex items-center gap-2 font-medium">
                                            {post.time}
                                            <span className="text-xs text-muted-foreground uppercase">
                                                {post.platform}
                                            </span>
                                        </span>
                                        <span className="line-clamp-2 text-muted-foreground">
                                            {post.excerpt}
                                        </span>
                                    </Link>
                                ))
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
