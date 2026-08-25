<?php

namespace App\Http\Controllers;

use App\Http\Requests\PersonaUpdateRequest;
use App\Models\Persona;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PersonaController extends Controller
{
    /**
     * Show the persona onboarding wizard, pre-filled with any saved answers.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('onboarding', [
            'persona' => $request->user()->persona,
            'options' => Persona::options(),
        ]);
    }

    /**
     * Create or update the user's persona.
     */
    public function update(PersonaUpdateRequest $request): RedirectResponse
    {
        $request->user()->persona()->updateOrCreate([], [
            ...$request->validated(),
            'completed_at' => now(),
        ]);

        return to_route('dashboard');
    }
}
