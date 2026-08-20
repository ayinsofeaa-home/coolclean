<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    // Step 1: Show resources/views/auth/login.blade.php.
    public function create(): View
    {
        return view('auth.login');
    }

    // Step 2: Receive the email and password from the login form.
    public function store(Request $request): RedirectResponse
    {
        // Stop here and return to the form if the input is invalid.
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Step 3: Find an active account by its EMAIL column.
        $user = User::where('EMAIL', $validated['email'])
            ->where('STATUS', 'ACTIVE')
            ->first();

        if ($user?->LOCKED_UNTIL?->isFuture()) {
            return back()->withErrors(['email' => 'Too many failed attempts. Try again later.'])->onlyInput('email');
        }

        // Step 4: Fail if the account was not found or the password is wrong.
        if (! $user || ! Hash::check($validated['password'], $user->getAuthPassword())) {
            if ($user) {
                $attempts = $user->FAILED_LOGIN_ATTEMPTS + 1;
                $user->update([
                    'FAILED_LOGIN_ATTEMPTS' => $attempts,
                    'LOCKED_UNTIL' => $attempts >= 5 ? now()->addMinutes(15) : null,
                ]);
            }
            // back() returns the browser to the login page.
            return back()
                ->withErrors([
                    'email' => 'The email address or password is incorrect.',
                ])
                // Keep the email, but never return the password to the form.
                ->onlyInput('email');
        }

        $user->update(['FAILED_LOGIN_ATTEMPTS' => 0, 'LOCKED_UNTIL' => null]);

        // Step 5: Remember this user in Laravel's login session.
        Auth::login($user);

        // Create a new session ID for security after login.
        $request->session()->regenerate();

        // Step 6: Send the logged-in user to the dashboard.
        if ($user->PASSWORD_CHANGE_REQUIRED) {
            return redirect()->route('admin.password.form')->with('success', 'Please change your temporary password.');
        }

        return redirect()->intended(route('dashboard'));
    }

    // Log the user out and return to the login page.
    public function destroy(Request $request): RedirectResponse
    {
        // Remove the authenticated user from the session.
        Auth::logout();

        // Delete the old session and create a new CSRF token.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
