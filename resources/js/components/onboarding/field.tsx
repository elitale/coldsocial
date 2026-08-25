import type { ReactNode } from 'react';

import { Label } from '@/components/ui/label';

interface FieldProps {
    label: string;
    hint?: string;
    htmlFor?: string;
    children: ReactNode;
}

export function Field({ label, hint, htmlFor, children }: FieldProps) {
    return (
        <div className="space-y-2">
            <Label htmlFor={htmlFor} className="text-sm font-medium">
                {label}
            </Label>
            {hint && (
                <p className="-mt-1 text-xs text-muted-foreground">{hint}</p>
            )}
            {children}
        </div>
    );
}
