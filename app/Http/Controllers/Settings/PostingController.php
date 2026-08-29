<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PostingUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostingController extends Controller
{
    /**
     * Show the posting preferences (timezone) form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/posting', [
            'timezone' => $request->user()->timezone,
            'timezones' => timezone_identifiers_list(),
        ]);
    }

    /**
     * Save the user's posting timezone.
     */
    public function update(PostingUpdateRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return to_route('posting.edit');
    }
}
