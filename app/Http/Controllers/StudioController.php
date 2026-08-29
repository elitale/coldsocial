<?php

namespace App\Http\Controllers;

use App\Ai\ProviderRequestException;
use App\Content\GenerateStudioDraft;
use App\Content\PlatformSpec;
use App\Content\ScheduleTime;
use App\Enums\PostStatus;
use App\Enums\SocialPlatform;
use App\Http\Requests\StudioGenerateRequest;
use App\Http\Requests\StudioStoreRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudioController extends Controller
{
    private const PLATFORM = SocialPlatform::Linkedin;

    /**
     * The composer: LinkedIn text post + live preview.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('studio/index', [
            'platform' => self::PLATFORM->value,
            'platformLabel' => self::PLATFORM->label(),
            'spec' => PlatformSpec::for(self::PLATFORM),
            'author' => [
                'name' => $request->user()->name,
                'headline' => $request->user()->persona?->headline,
            ],
            'timezone' => $request->user()->timezone ?? 'UTC',
            'generated' => $request->session()->get('generated'),
        ]);
    }

    /**
     * Generate a caption in the user's persona voice; flash it back to the composer.
     */
    public function generate(StudioGenerateRequest $request, GenerateStudioDraft $draft): RedirectResponse
    {
        $prompt = $request->string('prompt')->trim()->toString();

        try {
            $caption = $draft->for($request->user(), $prompt !== '' ? $prompt : null);
        } catch (ProviderRequestException $e) {
            return back()->withErrors(['prompt' => $e->getMessage()]);
        }

        return back()->with('generated', $caption);
    }

    /**
     * Persist the composed post as a draft, or as scheduled when a time is given.
     */
    public function store(StudioStoreRequest $request): RedirectResponse
    {
        $scheduledAt = null;
        $scheduledInput = $request->string('scheduled_at')->toString();

        if ($scheduledInput !== '') {
            $scheduledAt = ScheduleTime::fromUserInput($scheduledInput, $request->user()->timezone ?? 'UTC');

            if ($scheduledAt === null) {
                return back()->withErrors(['scheduled_at' => 'Choose a time in the future.']);
            }
        }

        $post = $request->user()->posts()->create([
            'platform' => self::PLATFORM->value,
            'body' => $request->string('body')->toString(),
        ]);

        if ($scheduledAt !== null) {
            $post->status = PostStatus::Scheduled;
            $post->scheduled_at = $scheduledAt;
            $post->save();
        }

        return to_route('posts.show', $post);
    }
}
