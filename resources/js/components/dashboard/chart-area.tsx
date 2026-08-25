import { Area, AreaChart, CartesianGrid, XAxis } from 'recharts';

import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    ChartContainer,
    ChartTooltip,
    ChartTooltipContent,
} from '@/components/ui/chart';
import type { ChartConfig } from '@/components/ui/chart';

const chartData = [
    { month: 'Mar', reach: 42000, engagement: 1820 },
    { month: 'Apr', reach: 51000, engagement: 2210 },
    { month: 'May', reach: 48500, engagement: 2040 },
    { month: 'Jun', reach: 62800, engagement: 2760 },
    { month: 'Jul', reach: 71200, engagement: 3180 },
    { month: 'Aug', reach: 86204, engagement: 4050 },
];

const chartConfig = {
    reach: { label: 'Reach', color: 'var(--chart-1)' },
    engagement: { label: 'Engagement', color: 'var(--chart-2)' },
} satisfies ChartConfig;

export function ChartArea() {
    return (
        <Card>
            <CardHeader>
                <CardTitle>Audience reach</CardTitle>
                <CardDescription>
                    Reach and engagement over the last 6 months
                </CardDescription>
            </CardHeader>
            <CardContent>
                <ChartContainer
                    config={chartConfig}
                    className="aspect-auto h-[260px] w-full"
                >
                    <AreaChart
                        data={chartData}
                        margin={{ left: 12, right: 12 }}
                    >
                        <defs>
                            <linearGradient
                                id="fillReach"
                                x1="0"
                                y1="0"
                                x2="0"
                                y2="1"
                            >
                                <stop
                                    offset="5%"
                                    stopColor="var(--color-reach)"
                                    stopOpacity={0.8}
                                />
                                <stop
                                    offset="95%"
                                    stopColor="var(--color-reach)"
                                    stopOpacity={0.1}
                                />
                            </linearGradient>
                            <linearGradient
                                id="fillEngagement"
                                x1="0"
                                y1="0"
                                x2="0"
                                y2="1"
                            >
                                <stop
                                    offset="5%"
                                    stopColor="var(--color-engagement)"
                                    stopOpacity={0.8}
                                />
                                <stop
                                    offset="95%"
                                    stopColor="var(--color-engagement)"
                                    stopOpacity={0.1}
                                />
                            </linearGradient>
                        </defs>
                        <CartesianGrid vertical={false} />
                        <XAxis
                            dataKey="month"
                            tickLine={false}
                            axisLine={false}
                            tickMargin={8}
                        />
                        <ChartTooltip
                            cursor={false}
                            content={<ChartTooltipContent indicator="dot" />}
                        />
                        <Area
                            dataKey="engagement"
                            type="natural"
                            fill="url(#fillEngagement)"
                            stroke="var(--color-engagement)"
                            stackId="a"
                        />
                        <Area
                            dataKey="reach"
                            type="natural"
                            fill="url(#fillReach)"
                            stroke="var(--color-reach)"
                            stackId="a"
                        />
                    </AreaChart>
                </ChartContainer>
            </CardContent>
        </Card>
    );
}
