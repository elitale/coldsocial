import { ShieldCheck } from 'lucide-react';

// Trust microcopy: users hand over personal info + social accounts here, so we state up front
// why we collect it and that it stays private (addresses the ICP privacy fears in icp.md).
export function WhyNote() {
    return (
        <div className="flex items-start gap-2 rounded-lg border border-dashed p-3 text-xs text-muted-foreground">
            <ShieldCheck className="mt-0.5 size-4 shrink-0" />
            <p>
                <span className="font-medium text-foreground">Why we ask</span>{' '}
                — coldsocial uses your answers to write posts that sound like
                you and reach the right audience. The more you share, the more
                on-brand your content. Everything is optional, private to your
                account, and never shared.
            </p>
        </div>
    );
}
