<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    /**
     * Show a month grid of the user's scheduled posts, placed on the day they
     * go out in the user's own timezone.
     */
    public function index(Request $request): Response
    {
        $timezone = $request->user()->timezone ?? 'UTC';
        $month = $request->string('month')->toString();

        $monthStart = (preg_match('/^\d{4}-\d{2}$/', $month) === 1
            ? Carbon::createFromFormat('Y-m-d', $month.'-01', $timezone)
            : Carbon::now($timezone))->startOfMonth();

        $monthEnd = $monthStart->clone()->endOfMonth();

        $posts = $request->user()->posts()
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [
                $monthStart->clone()->utc(),
                $monthEnd->clone()->utc(),
            ])
            ->orderBy('scheduled_at')
            ->get();

        return Inertia::render('calendar/index', [
            'month' => $monthStart->format('Y-m'),
            'monthLabel' => $monthStart->translatedFormat('F Y'),
            'timezone' => $timezone,
            'firstWeekday' => $monthStart->dayOfWeek,
            'daysInMonth' => $monthStart->daysInMonth,
            'today' => Carbon::now($timezone)->format('Y-m-d'),
            'prevMonth' => $monthStart->clone()->subMonth()->format('Y-m'),
            'nextMonth' => $monthStart->clone()->addMonth()->format('Y-m'),
            'postsByDay' => $this->groupByLocalDay($posts, $timezone),
        ]);
    }

    /**
     * Group posts by their scheduled day in the user's timezone.
     *
     * @param  Collection<int, Post>  $posts
     * @return Collection<string, Collection<int, array{id: int, platform: string, status: string, time: string, excerpt: string}>>
     */
    private function groupByLocalDay(Collection $posts, string $timezone): Collection
    {
        return $posts
            ->groupBy(fn (Post $post): string => $post->scheduled_at->clone()->setTimezone($timezone)->format('Y-m-d'))
            ->map(fn (Collection $dayPosts) => $dayPosts->map(fn (Post $post): array => [
                'id' => $post->id,
                'platform' => $post->platform,
                'status' => $post->status->value,
                'time' => $post->scheduled_at->clone()->setTimezone($timezone)->format('H:i'),
                'excerpt' => Str::limit($post->body, 40),
            ])->values());
    }
}
