<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    public function showChallenge()
    {
        // If no pending 2FA, redirect to login
        if (!session()->has('2fa:user_id')) {
            return redirect()->route('login');
        }
        
        return view('auth.2fa-challenge');
    }
    
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);
        
        $userId = session('2fa:user_id');
        $remember = session('2fa:remember', false);
        
        if (!$userId) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Session expired. Please login again.']);
        }
        
        $user = \App\Models\User::find($userId);
        
        if (!$user) {
            session()->forget(['2fa:user_id', '2fa:remember']);
            return redirect()->route('login')
                ->withErrors(['email' => 'User not found.']);
        }
        
        $google2fa = new Google2FA();
        $secret = decrypt($user->two_factor_secret);
        
        // Check if it's a recovery code
        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true) ?? [];
        
        if (in_array($request->code, $recoveryCodes)) {
            // Remove used recovery code
            $recoveryCodes = array_values(array_diff($recoveryCodes, [$request->code]));
            $user->two_factor_recovery_codes = encrypt(json_encode($recoveryCodes));
            $user->save();
            
            // Login user
            Auth::login($user, $remember);
            session()->forget(['2fa:user_id', '2fa:remember']);
            $request->session()->regenerate();
            
            // Reset failed attempts
            $user->resetFailedAttempts();
            
            // Log login activity
            ActivityLogService::logLogin();
            
            return redirect()->intended(route('dashboard'))
                ->with('info', 'You used a recovery code. Please generate new recovery codes.');
        }
        
        // Verify TOTP code
        $valid = $google2fa->verifyKey($secret, $request->code);
        
        if (!$valid) {
            return back()->withErrors(['code' => 'Invalid authentication code. Please try again.']);
        }
        
        // Login user
        Auth::login($user, $remember);
        session()->forget(['2fa:user_id', '2fa:remember']);
        $request->session()->regenerate();
        
        // Reset failed attempts
        $user->resetFailedAttempts();
        
        // Log login activity
        ActivityLogService::logLogin();
        
        return redirect()->intended(route('dashboard'));
    }
}
