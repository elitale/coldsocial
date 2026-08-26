<?php

namespace App\Http\Controllers;

use App\Ai\ProviderRequestException;
use App\Content\GenerateLinkedInDraft;
use App\Content\GenerateWeeklyDrafts;
use App\Content\RewriteDraft;
use App\Enums\PostStatus;
use App\Http\Requests\GeneratePostRequest;
use App\Http\Requests\PostUpdateRequest;
use App\Http\Requests\RegeneratePostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    /**
     * List the user's drafts, newest first.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('posts/index', [
            'posts' => $request->user()->posts()->latest()->get(),
        ]);
    }

    /**
     * Generate a LinkedIn draft from one of the user's updates.
     */
    public function store(GeneratePostRequest $request, GenerateLinkedInDraft $draft): RedirectResponse
    {
        $update = $request->user()->updates()->findOrFail($request->integer('update_id'));

        try {
            $post = $draft->for($update);
        } catch (ProviderRequestException $e) {
            return back()->withErrors(['generate' => $e->getMessage()]);
        }

        return to_route('posts.show', $post);
    }

    /**
     * Generate a week of drafts (persona + recent updates, varied angles).
     */
    public function week(Request $request, GenerateWeeklyDrafts $action): RedirectResponse
    {
        try {
            $action->forUser($request->user());
        } catch (ProviderRequestException $e) {
            return back()->withErrors(['generate' => $e->getMessage()]);
        }

        return to_route('posts.index');
    }

    /**
     * Show a single draft.
     */
    public function show(Request $request, Post $post): Response
    {
        abort_unless($post->user_id === $request->user()->id, 403);

        return Inertia::render('posts/show', [
            'post' => $post->load('sourceUpdate'),
        ]);
    }

    /**
     * Show the edit form for a draft.
     */
    public function edit(Request $request, Post $post): Response
    {
        abort_unless($post->user_id === $request->user()->id, 403);

        return Inertia::render('posts/edit', [
            'post' => $post,
        ]);
    }

    /**
     * Save edits to a draft's body.
     */
    public function update(PostUpdateRequest $request, Post $post): RedirectResponse
    {
        abort_unless($post->user_id === $request->user()->id, 403);

        $post->update($request->validated());

        return to_route('posts.show', $post);
    }

    /**
     * Rewrite a draft in place from a free-text instruction.
     */
    public function regenerate(RegeneratePostRequest $request, Post $post, RewriteDraft $rewrite): RedirectResponse
    {
        abort_unless($post->user_id === $request->user()->id, 403);

        try {
            $rewrite->apply($post, $request->string('instruction')->toString());
        } catch (ProviderRequestException $e) {
            return back()->withErrors(['regenerate' => $e->getMessage()]);
        }

        return to_route('posts.show', $post);
    }

    /**
     * Approve a draft — the gate before scheduling / publishing.
     */
    public function approve(Request $request, Post $post): RedirectResponse
    {
        abort_unless($post->user_id === $request->user()->id, 403);

        $post->status = PostStatus::Approved;
        $post->save();

        return to_route('posts.show', $post);
    }

    /**
     * Send an approved post back to draft.
     */
    public function unapprove(Request $request, Post $post): RedirectResponse
    {
        abort_unless($post->user_id === $request->user()->id, 403);

        $post->status = PostStatus::Draft;
        $post->save();

        return to_route('posts.show', $post);
    }

    /**
     * Delete one of the user's drafts.
     */
    public function destroy(Request $request, Post $post): RedirectResponse
    {
        abort_unless($post->user_id === $request->user()->id, 403);

        $post->delete();

        return to_route('posts.index');
    }
}
