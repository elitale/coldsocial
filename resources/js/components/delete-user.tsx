import { Form } from '@inertiajs/react';

import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { destroy } from '@/routes/profile';

export default function DeleteUser() {
    return (
        <div className="space-y-6">
            <HeadingSmall
                title="Delete account"
                description="Delete your account and all of its resources"
            />

            <div className="space-y-4 rounded-lg border border-destructive/40 bg-destructive/5 p-4">
                <div className="space-y-0.5 text-sm">
                    <p className="font-medium">Warning</p>
                    <p className="text-muted-foreground">
                        Once your account is deleted, all of its resources and
                        data are permanently removed. Enter your password to
                        confirm.
                    </p>
                </div>

                <Form
                    action={destroy.url()}
                    method="delete"
                    resetOnSuccess={['password']}
                    className="space-y-4"
                >
                    {({ processing, errors }) => (
                        <div className="grid max-w-sm gap-2">
                            <Label
                                htmlFor="delete-password"
                                className="sr-only"
                            >
                                Password
                            </Label>
                            <Input
                                id="delete-password"
                                type="password"
                                name="password"
                                placeholder="Password"
                                autoComplete="current-password"
                            />
                            <InputError message={errors.password} />
                            <Button
                                type="submit"
                                variant="destructive"
                                disabled={processing}
                                className="mt-2 w-fit"
                            >
                                Delete account
                            </Button>
                        </div>
                    )}
                </Form>
            </div>
        </div>
    );
}
