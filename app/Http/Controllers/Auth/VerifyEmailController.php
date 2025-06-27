<?php

/**
 * COMMENTED OUT - This controller is currently unused (admin-only ERP system)
 * 
 * Email verification functionality has been disabled for this ERP system.
 * The related routes in routes/auth.php have been commented out.
 * 
 * If you need to enable email verification in the future:
 * 1. Uncomment the email verification routes in routes/auth.php
 * 2. Uncomment the controller import in routes/auth.php
 * 3. Remove this comment block
 * 
 * Currently unused since: 2024-12 (route cleanup)
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
