import { Form, Head, router } from '@inertiajs/react';
import { useEffect, useMemo, useRef, useState } from 'react';

import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { edit, update } from '@/routes/posting';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Posting settings', href: edit().url },
];

const rawBrowserTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

/** Legacy IANA aliases some browsers still report, mapped to the modern canonical name. */
const TIMEZONE_ALIASES: Record<string, string> = {
    'Asia/Calcutta': 'Asia/Kolkata',
    'Asia/Katmandu': 'Asia/Kathmandu',
    'Asia/Rangoon': 'Asia/Yangon',
    'Asia/Saigon': 'Asia/Ho_Chi_Minh',
    'Asia/Ulan_Bator': 'Asia/Ulaanbaatar',
    'America/Buenos_Aires': 'America/Argentina/Buenos_Aires',
    'Europe/Kiev': 'Europe/Kyiv',
    'Pacific/Ponape': 'Pacific/Pohnpei',
    'Pacific/Truk': 'Pacific/Chuuk',
};

/** Resolve the browser's zone to a canonical IANA name the backend recognises (else UTC). */
function canonicalTimezone(tz: string, known: string[]): string {
    if (known.includes(tz)) {
        return tz;
    }

    const aliased = TIMEZONE_ALIASES[tz];

    if (aliased && known.includes(aliased)) {
        return aliased;
    }

    try {
        const resolved = new Intl.DateTimeFormat('en-US', {
            timeZone: tz,
        }).resolvedOptions().timeZone;

        if (known.includes(resolved)) {
            return resolved;
        }
    } catch {
        // Unknown zone — fall through.
    }

    return known.includes('UTC') ? 'UTC' : tz;
}

export default function Posting({
    timezone,
    timezones,
}: {
    timezone: string | null;
    timezones: string[];
}) {
    const browserTimezone = canonicalTimezone(rawBrowserTimezone, timezones);
    const [value, setValue] = useState(timezone ?? browserTimezone);
    const autoSaved = useRef(false);

    // First visit only: persist the browser's timezone so the user doesn't have to pick one.
    useEffect(() => {
        if (autoSaved.current || timezone) {
            return;
        }

        autoSaved.current = true;
        router.patch(
            update.url(),
            { timezone: browserTimezone },
            { preserveScroll: true },
        );
    }, [timezone, browserTimezone]);

    // Always keep the detected zone + current value selectable, even if they aren't in PHP's list.
    const options = useMemo(() => {
        const extras = [browserTimezone, value].filter(
            (tz) => !timezones.includes(tz),
        );

        return Array.from(new Set([...extras, ...timezones]));
    }, [browserTimezone, timezones, value]);

    // Group zones by IANA region (the part before the first "/") for a sectioned picker.
    const groups = useMemo(() => {
        const byRegion: Record<string, string[]> = {};

        for (const tz of options) {
            const region = tz.includes('/') ? tz.split('/')[0] : 'Other';
            (byRegion[region] ??= []).push(tz);
        }

        return Object.entries(byRegion)
            .map(([region, zones]) => [region, [...zones].sort()] as const)
            .sort((a, b) => a[0].localeCompare(b[0]));
    }, [options]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Posting settings" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="Posting timezone"
                        description="We'll schedule and show your posts in this timezone."
                    />

                    <Form
                        action={update.url()}
                        method="patch"
                        options={{ preserveScroll: true }}
                        className="space-y-6"
                    >
                        {({ processing, recentlySuccessful, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="timezone">Timezone</Label>
                                    <div className="flex gap-2">
                                        <Select
                                            name="timezone"
                                            value={value}
                                            onValueChange={setValue}
                                        >
                                            <SelectTrigger
                                                id="timezone"
                                                className="w-full"
                                            >
                                                <SelectValue placeholder="Select a timezone" />
                                            </SelectTrigger>
                                            <SelectContent position="popper">
                                                {groups.map(
                                                    ([region, zones]) => (
                                                        <SelectGroup
                                                            key={region}
                                                        >
                                                            <SelectLabel>
                                                                {region}
                                                            </SelectLabel>
                                                            {zones.map((tz) => (
                                                                <SelectItem
                                                                    key={tz}
                                                                    value={tz}
                                                                >
                                                                    {tz}
                                                                </SelectItem>
                                                            ))}
                                                        </SelectGroup>
                                                    ),
                                                )}
                                            </SelectContent>
                                        </Select>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() =>
                                                setValue(browserTimezone)
                                            }
                                        >
                                            Detect
                                        </Button>
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        Detected from your browser:{' '}
                                        {browserTimezone}
                                    </p>
                                    <InputError message={errors.timezone} />
                                </div>

                                <div className="flex items-center gap-4">
                                    <Button type="submit" disabled={processing}>
                                        Save
                                    </Button>
                                    {recentlySuccessful && (
                                        <p className="text-sm text-muted-foreground">
                                            Saved
                                        </p>
                                    )}
                                </div>
                            </>
                        )}
                    </Form>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
