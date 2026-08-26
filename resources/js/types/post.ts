import type { Update } from '@/types/update';

export interface Post {
    id: number;
    update_id: number | null;
    platform: string;
    status: 'draft' | 'approved';
    body: string;
    created_at: string;
    source_update?: Update | null;
}
