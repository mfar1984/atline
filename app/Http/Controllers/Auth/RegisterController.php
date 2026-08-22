<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Public client self-registration.
 *
 * ── What it creates ──
 *
 * Both halves of a client, in one transaction:
 *
 *   users   — approval_status = 'pending', is_active = false, role_id = null
 *   clients — is_active = false, user_id = the new user
 *
 * Creating the `clients` row at registration rather than at approval is what lets
 * an administrator see the company name, address, state and phone before deciding.
 * Approval is a judgement about a real organisation, and the data has to be
 * visible where the decision is made — external/settings?tab=clients.
 *
 * The field set therefore mirrors the Add Client wizard on that screen, not
 * /settings/users/create. That form only collects name, email, password and role,
 * which is right for a staff account and useless for vetting a client.
 *
 * ── What it deliberately does NOT do ──
 *
 * No role is assigned here. The administrator picks it at approval time, the same
 * way the Add Client wizard already does, so a registrant cannot influence their
 * own permissions and a missing 'Client' role row cannot produce a role-less
 * account.
 *
 * Nothing is logged in. is_active stays false until approved, so even if a
 * session were somehow established the login gate would reject it.
 */
class RegisterController extends Controller
{
    /** Mirrors the Type dropdown in the Add Client wizard. */
    private const ORGANIZATION_TYPES = ['gov', 'ngo', 'company'];

    public function showRegistrationForm()
    {
        if (! $this->registrationOpen()) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Registration is currently closed. Please contact the administrator.']);
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        if (! $this->registrationOpen()) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Registration is currently closed. Please contact the administrator.']);
        }

        $minLength = (int) SystemSetting::getValue('security', 'password_min_length', 8);

        $validated = $request->validate([
            // Company details — the same columns the Add Client wizard writes.
            'name' => 'required|string|max:255',
            'organization_type' => 'nullable|string|in:'.implode(',', self::ORGANIZATION_TYPES),
            'address_1' => 'nullable|string|max:255',
            'address_2' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:10',
            'district' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'phone' => 'required|string|max:50',
            'client_email' => 'nullable|email|max:255',
            'contact_person' => 'required|string|max:255',

            // Login details.
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'string', 'confirmed', 'min:'.$minLength],
        ], [
            'email.unique' => 'An account with this email already exists. Try signing in instead.',
            'phone.required' => 'A phone number is required so we can verify your organisation.',
            'contact_person.required' => 'Please give us a contact person.',
        ]);

        /*
         * One transaction. A user without its client row would be invisible on the
         * approval screen, and a client row without its user could never sign in —
         * either half on its own is a support call, so neither is allowed to exist.
         */
        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['contact_person'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                // No role until an administrator chooses one.
                'role_id' => null,
                // Both gates closed. is_active is what LoginController checks
                // first; approval_status is what tells the applicant why.
                'is_active' => false,
                'approval_status' => User::APPROVAL_PENDING,
            ]);

            Client::create([
                'name' => $validated['name'],
                'user_id' => $user->id,
                'organization_type' => $validated['organization_type'] ?? null,
                'address_1' => $validated['address_1'] ?? null,
                'address_2' => $validated['address_2'] ?? null,
                'postcode' => $validated['postcode'] ?? null,
                'district' => $validated['district'] ?? null,
                'state' => $validated['state'] ?? null,
                'country' => $validated['country'] ?? 'Malaysia',
                'website' => $validated['website'] ?? null,
                'phone' => $validated['phone'],
                // The company's own address, which may differ from the login email.
                'email' => $validated['client_email'] ?? $validated['email'],
                'contact_person' => $validated['contact_person'],
                'is_active' => false,
            ]);

            return $user;
        });

        // Outside the transaction: a logging failure must not roll back a
        // registration the applicant has already been told nothing about yet.
        try {
            ActivityLogService::logCreate(
                $user,
                'external_settings_client',
                "Client self-registration from {$validated['name']} ({$validated['email']}) — awaiting approval"
            );
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: '.$e->getMessage());
        }

        return redirect()->route('login')->with('registered', true);
    }

    /**
     * Whether self-registration is switched on.
     *
     * Read from the same SystemSetting store the rest of the application uses, so
     * it can be turned off without a deploy. Defaults to true — nothing writes
     * this key yet, and a missing setting should not be an outage.
     */
    private function registrationOpen(): bool
    {
        return (bool) SystemSetting::getValue('security', 'allow_client_registration', true);
    }
}
