<?php

/**
 * COMMENTED OUT - This controller is currently unused (admin-only ERP system)
 * 
 * Email verification prompt functionality has been disabled for this ERP system.
 * The related routes in routes/auth.php have been commented out.
 * 
 * If you need to enable email verification prompts in the future:
 * 1. Uncomment the email verification routes in routes/auth.php
 * 2. Uncomment the controller import in routes/auth.php
 * 3. Remove this comment block
 * 
 * Currently unused since: 2024-12 (route cleanup)
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(route('dashboard', absolute: false))
                    : view('auth.verify-email');
    }
}
