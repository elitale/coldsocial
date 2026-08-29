<?php

namespace App\Http\Controllers;

use App\Connections\LinkedInOAuth;
use App\Enums\SocialPlatform;
use App\Models\PlatformConnection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ConnectionController extends Controller
{
    /**
     * The Connections hub: every supported platform + this user's status.
     */
    public function index(Request $request): Response
    {
        $connections = $request->user()->connections()->get()
            ->keyBy(fn (PlatformConnection $connection): string => $connection->platform->value);

        $platforms = array_map(function (SocialPlatform $platform) use ($connections): array {
            $connection = $connections->get($platform->value);

            return [
                'key' => $platform->value,
                'label' => $platform->label(),
                'status' => $connection !== null
                    ? 'connected'
                    : ($platform->connectable() ? 'available' : 'coming_soon'),
                'accountName' => $connection?->display_name,
            ];
        }, SocialPlatform::cases());

        return Inertia::render('connections/index', [
            'platforms' => $platforms,
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ]);
    }

    /**
     * Send the user to the platform's OAuth consent screen.
     */
    public function redirect(Request $request, SocialPlatform $platform, LinkedInOAuth $oauth): RedirectResponse
    {
        abort_unless($platform->connectable(), 404);

        $state = Str::random(40);
        $request->session()->put('linkedin_oauth_state', $state);

        return redirect()->away($oauth->redirectUrl($state));
    }

    /**
     * Handle the OAuth callback and store the connection.
     */
    public function callback(Request $request, SocialPlatform $platform, LinkedInOAuth $oauth): RedirectResponse
    {
        abort_unless($platform->connectable(), 404);

        $expectedState = $request->session()->pull('linkedin_oauth_state');

        if ($request->filled('error')
            || ! $request->filled('code')
            || $request->string('state')->toString() !== (string) $expectedState) {
            return to_route('connections.index')
                ->with('error', 'Couldn’t connect '.$platform->label().' — access was denied or cancelled.');
        }

        try {
            $profile = $oauth->connect($request->string('code')->toString());
        } catch (\Throwable) {
            return to_route('connections.index')
                ->with('error', 'Couldn’t connect '.$platform->label().' — please try again.');
        }

        $request->user()->connections()->updateOrCreate(
            ['platform' => $platform],
            $profile,
        );

        return to_route('connections.index')
            ->with('success', $platform->label().' connected as '.$profile['display_name'].'.');
    }
}
