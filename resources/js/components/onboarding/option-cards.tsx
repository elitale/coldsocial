import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { cn } from '@/lib/utils';

interface OptionCardsProps {
    name: string;
    options: Record<string, string>;
    value: string;
    onChange: (value: string) => void;
    columns?: 2 | 3;
}

export function OptionCards({
    name,
    options,
    value,
    onChange,
    columns = 2,
}: OptionCardsProps) {
    return (
        <RadioGroup
            value={value}
            onValueChange={onChange}
            className={cn(
                'grid gap-3',
                columns === 3 ? 'sm:grid-cols-3' : 'sm:grid-cols-2',
            )}
        >
            {Object.entries(options).map(([key, label]) => (
                <Label
                    key={key}
                    htmlFor={`${name}-${key}`}
                    className={cn(
                        'flex cursor-pointer items-center gap-3 rounded-lg border p-4 text-sm font-normal transition-colors',
                        value === key
                            ? 'border-primary bg-accent'
                            : 'hover:bg-accent/50',
                    )}
                >
                    <RadioGroupItem id={`${name}-${key}`} value={key} />
                    <span>{label}</span>
                </Label>
            ))}
        </RadioGroup>
    );
}
