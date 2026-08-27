import type { Update } from '@/types/update';

export interface Post {
    id: number;
    update_id: number | null;
    platform: string;
    status: 'draft' | 'approved' | 'scheduled';
    body: string;
    scheduled_at: string | null;
    created_at: string;
    source_update?: Update | null;
}
