<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Find user by email
        $user = User::where('email', $credentials['email'])->first();
        
        // Check if user exists
        if (!$user) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }
        
        // Check if account is locked
        if ($user->isLocked()) {
            $remainingMinutes = $user->getLockoutRemainingMinutes();
            return back()->withErrors([
                'email' => "Your account is locked due to too many failed login attempts. Please try again in {$remainingMinutes} minute(s).",
            ])->onlyInput('email');
        }
        
        /*
         * Registration approval, checked BEFORE is_active.
         *
         * Order matters. A pending registrant also has is_active = false, so
         * without these two branches they would be told "your account has been
         * deactivated" — describing a state the account has never been in, and
         * sending them to chase an administrator about the wrong problem.
         *
         * Both run before the password is verified, matching how the lockout and
         * is_active gates already behave. That does mean the message confirms the
         * email is registered, but so does every other branch above, and telling
         * an applicant "still waiting" only after they type the right password
         * would be worse.
         */
        if ($user->isPendingApproval()) {
            return back()->withErrors([
                'email' => 'Your registration is still waiting for approval. You will be able to sign in once an administrator has approved it.',
            ])->onlyInput('email');
        }

        if ($user->isRejected()) {
            $reason = trim((string) $user->rejection_reason);

            return back()->withErrors([
                'email' => $reason !== ''
                    ? "Your registration was not approved: {$reason}"
                    : 'Your registration was not approved. Please contact the administrator.',
            ])->onlyInput('email');
        }

        // Check if user is active
        if (!$user->is_active) {
            return back()->withErrors([
                'email' => 'Your account has been deactivated. Please contact administrator.',
            ])->onlyInput('email');
        }

        // Verify password first (without logging in)
        if (!Auth::validate($credentials)) {
            // Failed login - increment attempts
            $user->incrementFailedAttempts();
            
            // Log failed login attempt
            try {
                ActivityLogService::logFailedLogin($credentials['email']);
            } catch (\Exception $e) {
                \Log::error('Activity logging failed: ' . $e->getMessage());
            }
            
            // Get max attempts for message
            $maxAttempts = SystemSetting::getValue('security', 'max_login_attempts', 5);
            $remainingAttempts = max(0, $maxAttempts - $user->failed_login_attempts);
            
            // Check if now locked
            if ($user->isLocked()) {
                $lockoutDuration = SystemSetting::getValue('security', 'lockout_duration', 15);
                return back()->withErrors([
                    'email' => "Too many failed login attempts. Your account has been locked for {$lockoutDuration} minutes.",
                ])->onlyInput('email');
            }

            return back()->withErrors([
                'email' => "Invalid credentials. You have {$remainingAttempts} attempt(s) remaining before your account is locked.",
            ])->onlyInput('email');
        }
        
        // Check if user has 2FA enabled
        if ($user->two_factor_confirmed_at && $user->two_factor_secret) {
            // Store user ID in session for 2FA verification
            $request->session()->put('2fa:user_id', $user->id);
            $request->session()->put('2fa:remember', $request->boolean('remember'));
            
            return redirect()->route('2fa.challenge');
        }
        
        // No 2FA - login directly
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        
        // Reset failed attempts on successful login
        $user->resetFailedAttempts();
        
        // Log login activity
        ActivityLogService::logLogin();
        
        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        // Log logout activity before logging out
        if (Auth::check()) {
            ActivityLogService::logLogout();
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
