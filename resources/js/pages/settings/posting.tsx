import { Form, Head, router } from '@inertiajs/react';
import { useEffect, useMemo, useRef, useState } from 'react';

import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { edit, update } from '@/routes/posting';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Posting settings', href: edit().url },
];

const browserTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

export default function Posting({
    timezone,
    timezones,
}: {
    timezone: string | null;
    timezones: string[];
}) {
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
    }, [timezone]);

    // Keep the current value selectable even if it's an alias not in PHP's canonical list.
    const options = useMemo(
        () => (timezones.includes(value) ? timezones : [value, ...timezones]),
        [timezones, value],
    );

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
                                        <select
                                            id="timezone"
                                            name="timezone"
                                            value={value}
                                            onChange={(e) =>
                                                setValue(e.target.value)
                                            }
                                            className="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 dark:bg-input/30"
                                        >
                                            {options.map((tz) => (
                                                <option key={tz} value={tz}>
                                                    {tz}
                                                </option>
                                            ))}
                                        </select>
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
