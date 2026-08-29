import { Badge } from '@/components/ui/badge';
import type { Post } from '@/types/post';

const LABELS: Record<Post['status'], string> = {
    draft: 'Draft',
    approved: 'Approved',
    scheduled: 'Scheduled',
};

const VARIANTS: Record<Post['status'], 'default' | 'secondary' | 'outline'> = {
    draft: 'outline',
    approved: 'secondary',
    scheduled: 'default',
};

export function PostStatusBadge({ status }: { status: Post['status'] }) {
    return <Badge variant={VARIANTS[status]}>{LABELS[status]}</Badge>;
}
