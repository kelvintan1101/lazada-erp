<?php

/**
 * COMMENTED OUT - This controller is currently unused (admin-only ERP system)
 * 
 * Password confirmation functionality has been disabled for this ERP system.
 * The related routes in routes/auth.php have been commented out.
 * 
 * If you need to enable password confirmation in the future:
 * 1. Uncomment the password confirmation routes in routes/auth.php
 * 2. Uncomment the controller import in routes/auth.php
 * 3. Remove this comment block
 * 
 * Currently unused since: 2024-12 (route cleanup)
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password view.
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Confirm the user's password.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
