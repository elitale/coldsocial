<?php

namespace App\Http\Controllers;

use App\Ai\ProviderRequestException;
use App\Content\GenerateLinkedInDraft;
use App\Http\Requests\GeneratePostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
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
     * Show a single draft.
     */
    public function show(Request $request, Post $post): Response
    {
        abort_unless($post->user_id === $request->user()->id, 403);

        return Inertia::render('posts/show', [
            'post' => $post->load('sourceUpdate'),
        ]);
    }
}
