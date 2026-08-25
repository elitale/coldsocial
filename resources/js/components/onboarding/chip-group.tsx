import { cn } from '@/lib/utils';

interface ChipGroupProps {
    options: Record<string, string>;
    values: string[];
    onChange: (values: string[]) => void;
    max?: number;
}

export function ChipGroup({ options, values, onChange, max }: ChipGroupProps) {
    const toggle = (key: string) => {
        if (values.includes(key)) {
            onChange(values.filter((value) => value !== key));

            return;
        }

        if (!max || values.length < max) {
            onChange([...values, key]);
        }
    };

    return (
        <div className="flex flex-wrap gap-2">
            {Object.entries(options).map(([key, label]) => {
                const active = values.includes(key);

                return (
                    <button
                        key={key}
                        type="button"
                        aria-pressed={active}
                        onClick={() => toggle(key)}
                        className={cn(
                            'rounded-full border px-3 py-1.5 text-sm transition-colors',
                            active
                                ? 'border-primary bg-primary text-primary-foreground'
                                : 'hover:bg-accent',
                        )}
                    >
                        {label}
                    </button>
                );
            })}
        </div>
    );
}
