import { TrendingDown, TrendingUp } from 'lucide-react';

import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardAction,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

type Metric = {
    label: string;
    value: string;
    delta: string;
    trend: 'up' | 'down';
    hint: string;
    sub: string;
};

const metrics: Metric[] = [
    {
        label: 'Total followers',
        value: '12,480',
        delta: '+8.2%',
        trend: 'up',
        hint: 'Growing this month',
        sub: 'Across all connected platforms',
    },
    {
        label: 'Engagement rate',
        value: '4.7%',
        delta: '+1.1%',
        trend: 'up',
        hint: 'More interactions per post',
        sub: 'Likes, comments and shares',
    },
    {
        label: 'Posts published',
        value: '38',
        delta: '-6.0%',
        trend: 'down',
        hint: 'Slightly fewer than last month',
        sub: 'Scheduled and published content',
    },
    {
        label: 'Reach',
        value: '86,204',
        delta: '+12.5%',
        trend: 'up',
        hint: 'Strong reach this month',
        sub: 'Unique accounts reached',
    },
];

export function SectionCards() {
    return (
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {metrics.map((metric) => {
                const TrendIcon =
                    metric.trend === 'up' ? TrendingUp : TrendingDown;

                return (
                    <Card key={metric.label}>
                        <CardHeader>
                            <CardDescription>{metric.label}</CardDescription>
                            <CardTitle className="text-2xl font-semibold tabular-nums">
                                {metric.value}
                            </CardTitle>
                            <CardAction>
                                <Badge variant="outline">
                                    <TrendIcon className="size-3" />
                                    {metric.delta}
                                </Badge>
                            </CardAction>
                        </CardHeader>
                        <CardFooter className="flex-col items-start gap-1 text-sm">
                            <div className="flex items-center gap-1.5 font-medium">
                                {metric.hint}
                                <TrendIcon className="size-4" />
                            </div>
                            <div className="text-muted-foreground">
                                {metric.sub}
                            </div>
                        </CardFooter>
                    </Card>
                );
            })}
        </div>
    );
}
