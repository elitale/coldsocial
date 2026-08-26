<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateStoreRequest;
use App\Models\Update;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UpdateController extends Controller
{
    /**
     * List the signed-in user's captured updates, newest first.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('updates/index', [
            'updates' => $request->user()->updates()->latest()->get(),
        ]);
    }

    /**
     * Capture a new update.
     */
    public function store(UpdateStoreRequest $request): RedirectResponse
    {
        $request->user()->updates()->create($request->validated());

        return to_route('updates.index');
    }

    /**
     * Delete one of the user's updates.
     */
    public function destroy(Request $request, Update $update): RedirectResponse
    {
        abort_unless($update->user_id === $request->user()->id, 403);

        $update->delete();

        return to_route('updates.index');
    }
}
