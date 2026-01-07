<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class ProfileController extends Controller
{
    /**
     * Show the profile page
     */
    public function index()
    {
        $user = Auth::user();
        $require2FA = SystemSetting::getValue('security', 'require_2fa', false);
        $has2FAEnabled = !empty($user->two_factor_confirmed_at);
        $passwordMinLength = SystemSetting::getValue('security', 'password_min_length', 8);
        
        return view('profile.index', compact('user', 'require2FA', 'has2FAEnabled', 'passwordMinLength'));
    }

    /**
     * Update profile information
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return redirect()->route('profile.index')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        $minLength = SystemSetting::getValue('security', 'password_min_length', 8);
        
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|confirmed|min:' . $minLength,
        ]);

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Log password change
        try {
            ActivityLogService::logPasswordChange();
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        return redirect()->route('profile.index')
            ->with('success', 'Password updated successfully.');
    }

    /**
     * Show 2FA setup page
     */
    public function show2FASetup()
    {
        $user = Auth::user();
        
        // If already enabled, show status
        if (!empty($user->two_factor_confirmed_at)) {
            return redirect()->route('profile.index')
                ->with('info', 'Two-factor authentication is already enabled.');
        }

        // Generate secret if not exists
        $google2fa = new Google2FA();
        
        if (empty($user->two_factor_secret)) {
            $secret = $google2fa->generateSecretKey();
            $user->update(['two_factor_secret' => encrypt($secret)]);
        } else {
            $secret = decrypt($user->two_factor_secret);
        }

        // Generate QR code
        $companyName = SystemSetting::getValue('company', 'name', 'Atline System');
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            $companyName,
            $user->email,
            $secret
        );

        // Generate SVG QR code
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        return view('profile.2fa-setup', compact('user', 'secret', 'qrCodeSvg'));
    }

    /**
     * Enable 2FA
     */
    public function enable2FA(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        if (empty($user->two_factor_secret)) {
            return back()->withErrors(['code' => 'Please set up 2FA first.']);
        }

        $google2fa = new Google2FA();
        $secret = decrypt($user->two_factor_secret);
        
        $valid = $google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'Invalid verification code. Please try again.']);
        }

        // Generate recovery codes
        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = strtoupper(bin2hex(random_bytes(4))) . '-' . strtoupper(bin2hex(random_bytes(4)));
        }

        $user->update([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
        ]);

        // Log 2FA enabled
        try {
            ActivityLogService::log2FAChange('enabled');
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        return redirect()->route('profile.2fa.recovery')
            ->with('success', 'Two-factor authentication enabled successfully.');
    }

    /**
     * Show recovery codes
     */
    public function showRecoveryCodes()
    {
        $user = Auth::user();
        
        if (empty($user->two_factor_confirmed_at)) {
            return redirect()->route('profile.index')
                ->with('error', 'Two-factor authentication is not enabled.');
        }

        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);

        return view('profile.2fa-recovery', compact('user', 'recoveryCodes'));
    }

    /**
     * Regenerate recovery codes
     */
    public function regenerateRecoveryCodes()
    {
        $user = Auth::user();
        
        if (empty($user->two_factor_confirmed_at)) {
            return redirect()->route('profile.index')
                ->with('error', 'Two-factor authentication is not enabled.');
        }

        // Generate new recovery codes
        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = strtoupper(bin2hex(random_bytes(4))) . '-' . strtoupper(bin2hex(random_bytes(4)));
        }

        $user->update([
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
        ]);

        return redirect()->route('profile.2fa.recovery')
            ->with('success', 'Recovery codes regenerated successfully.');
    }

    /**
     * Disable 2FA
     */
    public function disable2FA(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'password' => 'required',
        ]);

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password is incorrect.']);
        }

        // Check if 2FA is required by system
        $require2FA = SystemSetting::getValue('security', 'require_2fa', false);
        if ($require2FA) {
            return back()->withErrors(['password' => 'Two-factor authentication is required by system policy and cannot be disabled.']);
        }

        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        // Log 2FA disabled
        try {
            ActivityLogService::log2FAChange('disabled');
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        return redirect()->route('profile.index')
            ->with('success', 'Two-factor authentication disabled successfully.');
    }
}
