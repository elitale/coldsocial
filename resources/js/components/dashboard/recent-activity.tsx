import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

type Post = {
    title: string;
    platform: string;
    status: 'Published' | 'Scheduled' | 'Draft';
    reach: string;
    engagement: string;
};

const posts: Post[] = [
    {
        title: 'Behind the scenes of our latest launch',
        platform: 'LinkedIn',
        status: 'Published',
        reach: '18,204',
        engagement: '5.8%',
    },
    {
        title: '3 lessons from scaling to 10k users',
        platform: 'Instagram',
        status: 'Published',
        reach: '12,860',
        engagement: '4.2%',
    },
    {
        title: 'Founder Q&A — ask me anything',
        platform: 'TikTok',
        status: 'Scheduled',
        reach: '—',
        engagement: '—',
    },
    {
        title: 'Weekly roundup: what we shipped',
        platform: 'YouTube',
        status: 'Draft',
        reach: '—',
        engagement: '—',
    },
];

const statusVariant: Record<
    Post['status'],
    'default' | 'secondary' | 'outline'
> = {
    Published: 'default',
    Scheduled: 'secondary',
    Draft: 'outline',
};

export function RecentActivity() {
    return (
        <Card>
            <CardHeader>
                <CardTitle>Recent posts</CardTitle>
                <CardDescription>
                    Your latest content and how it performed
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Post</TableHead>
                            <TableHead>Platform</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead className="text-right">Reach</TableHead>
                            <TableHead className="text-right">
                                Engagement
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {posts.map((post) => (
                            <TableRow key={post.title}>
                                <TableCell className="max-w-[260px] truncate font-medium">
                                    {post.title}
                                </TableCell>
                                <TableCell className="text-muted-foreground">
                                    {post.platform}
                                </TableCell>
                                <TableCell>
                                    <Badge variant={statusVariant[post.status]}>
                                        {post.status}
                                    </Badge>
                                </TableCell>
                                <TableCell className="text-right tabular-nums">
                                    {post.reach}
                                </TableCell>
                                <TableCell className="text-right tabular-nums">
                                    {post.engagement}
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </CardContent>
        </Card>
    );
}
