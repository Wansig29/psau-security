<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        // If already authenticated, redirect away from login page (FIX 4)
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session (FIX 6 — Proper Logout).
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Step 1: Log the logout event
        if (Auth::check()) {
            Log::info('User logged out: ' . Auth::user()->email . ' at ' . now());
        }

        // Step 2: Logout from the guard
        Auth::guard('web')->logout();

        // Step 3: Completely destroy the session
        $request->session()->invalidate();

        // Step 4: Regenerate the CSRF token
        $request->session()->regenerateToken();

        // Step 5: Redirect to login with no-cache headers so
        //         the browser cannot restore authenticated pages via Back button
        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully.')
            ->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
                'Pragma'        => 'no-cache',
                'Expires'       => '0',
            ]);
    }
}

