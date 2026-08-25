import { Plus, Trash2 } from 'lucide-react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { CustomLink } from '@/types/persona';

interface CustomLinksProps {
    links: CustomLink[];
    onChange: (links: CustomLink[]) => void;
    errors?: Record<string, string | undefined>;
    max?: number;
}

export function CustomLinks({
    links,
    onChange,
    errors,
    max = 10,
}: CustomLinksProps) {
    const update = (index: number, patch: Partial<CustomLink>) => {
        onChange(
            links.map((link, i) =>
                i === index ? { ...link, ...patch } : link,
            ),
        );
    };

    const add = () => onChange([...links, { label: '', url: '' }]);

    const remove = (index: number) =>
        onChange(links.filter((_, i) => i !== index));

    return (
        <div className="space-y-3">
            {links.map((link, index) => (
                <div key={index} className="flex items-start gap-2">
                    <div className="w-1/3 space-y-1.5">
                        <Input
                            aria-label={`Custom link ${index + 1} label`}
                            value={link.label}
                            onChange={(e) =>
                                update(index, { label: e.target.value })
                            }
                            placeholder="Substack, Podcast…"
                        />
                        <InputError
                            message={errors?.[`custom_links.${index}.label`]}
                        />
                    </div>
                    <div className="flex-1 space-y-1.5">
                        <Input
                            aria-label={`Custom link ${index + 1} URL`}
                            type="url"
                            inputMode="url"
                            value={link.url}
                            onChange={(e) =>
                                update(index, { url: e.target.value })
                            }
                            placeholder="https://…"
                        />
                        <InputError
                            message={errors?.[`custom_links.${index}.url`]}
                        />
                    </div>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        aria-label={`Remove custom link ${index + 1}`}
                        onClick={() => remove(index)}
                    >
                        <Trash2 className="size-4" />
                    </Button>
                </div>
            ))}

            {links.length < max && (
                <Button type="button" variant="outline" size="sm" onClick={add}>
                    <Plus className="size-4" />
                    Add link
                </Button>
            )}
        </div>
    );
}
