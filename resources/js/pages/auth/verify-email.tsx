import { Form, Head, Link } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/auth-layout';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

export default function VerifyEmail({ status }: { status?: string }) {
    return (
        <AuthLayout
            title="Verify email"
            description="Please verify your email address by clicking the link we just emailed to you"
        >
            <Head title="Email verification" />

            {status === 'verification-link-sent' && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    A new verification link has been sent to your email address.
                </div>
            )}

            <Form
                action={send.url()}
                method="post"
                className="flex flex-col items-center gap-6"
            >
                {({ processing }) => (
                    <>
                        <Button
                            type="submit"
                            disabled={processing}
                            variant="secondary"
                        >
                            {processing
                                ? 'Sending…'
                                : 'Resend verification email'}
                        </Button>

                        <Link
                            href={logout()}
                            as="button"
                            className="text-sm text-muted-foreground underline underline-offset-4 hover:text-foreground"
                        >
                            Log out
                        </Link>
                    </>
                )}
            </Form>
        </AuthLayout>
    );
}
