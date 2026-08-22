{{--
    Client self-registration.

    A standalone document rather than @extends('layouts.app'), matching
    auth/login.blade.php — the app layout carries a sidebar, header and footer that
    assume an authenticated user.

    The field set mirrors the Add Client wizard in
    external/settings?tab=clients, NOT /settings/users/create. Those two forms
    collect different things on purpose: the users form wants name, email, password
    and role, which is right for a staff account and tells an administrator nothing
    about whether an organisation is real. Approval is a judgement about a company,
    so registration has to gather what that judgement needs — company name, type,
    address, state, phone and a named contact.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register — {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    @vite(['resources/css/app.css'])

    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Poppins, sans-serif;
            background: #f3f4f6;
            color: #1f2937;
        }
        .reg-wrap { min-height: 100vh; display: flex; align-items: flex-start; justify-content: center; padding: 32px 16px; }
        .reg-card { width: 100%; max-width: 720px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(17,24,39,.08); }
        .reg-head { padding: 20px 24px; border-bottom: 1px solid #e5e7eb; background: #f9fafb; display: flex; align-items: center; gap: 12px; }
        .reg-badge { width: 40px; height: 40px; border-radius: 8px; background: #0ea5e9; display: flex; align-items: center; justify-content: center; flex: 0 0 auto; }
        .reg-badge .material-symbols-outlined { color: #fff; font-size: 22px; }
        .reg-title { margin: 0; font-size: 15px; font-weight: 600; color: #111827; }
        .reg-sub { margin: 2px 0 0; font-size: 11.5px; color: #6b7280; }
        .reg-body { padding: 22px 24px; }
        .reg-section { font-size: 11px; font-weight: 600; color: #0ea5e9; text-transform: uppercase; letter-spacing: .06em; margin: 0 0 12px; }
        .reg-section + .reg-grid { margin-bottom: 18px; }
        .reg-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .reg-grid-1 { grid-template-columns: 1fr; }
        .reg-field label { display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 6px; }
        .reg-field .req { color: #ef4444; }
        .reg-field input, .reg-field select {
            width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px;
            font-family: Poppins, sans-serif; font-size: 12px; color: #1f2937; outline: none; background: #fff;
        }
        .reg-field input:focus, .reg-field select:focus { border-color: #3b82f6; }
        .reg-field input.has-error, .reg-field select.has-error { border-color: #ef4444; background: #fef2f2; }
        .reg-error { display: block; margin-top: 4px; font-size: 10.5px; color: #dc2626; }
        .reg-hint { display: block; margin-top: 4px; font-size: 10.5px; color: #6b7280; }
        .reg-rule { height: 1px; background: #e5e7eb; border: 0; margin: 20px 0; }
        .reg-foot { padding: 16px 24px; border-top: 1px solid #e5e7eb; background: #f9fafb; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
        .reg-note { font-size: 11px; color: #6b7280; margin: 0; }
        .reg-note a { color: #2563eb; text-decoration: none; font-weight: 500; }
        .reg-submit {
            display: inline-flex; align-items: center; gap: 8px; padding: 0 18px; min-height: 36px;
            background: #2563eb; color: #fff; border: 0; border-radius: 6px;
            font-family: Poppins, sans-serif; font-size: 12px; font-weight: 500; cursor: pointer;
        }
        .reg-submit:hover { background: #1d4ed8; }
        .reg-alert { margin: 0 0 18px; padding: 12px 14px; border-radius: 6px; font-size: 11.5px; line-height: 1.5; }
        .reg-alert-info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
        .reg-alert-bad { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        @media (max-width: 640px) { .reg-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="reg-wrap">
    <div class="reg-card">
        <div class="reg-head">
            <div class="reg-badge"><span class="material-symbols-outlined">business</span></div>
            <div>
                <h1 class="reg-title">Register as a Client</h1>
                <p class="reg-sub">Tell us about your organisation. An administrator reviews every request.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('register') }}" novalidate>
            @csrf
            <div class="reg-body">

                {{-- Said before the form, not after submitting. Somebody filling in
                     eleven fields deserves to know up front that they cannot sign
                     in immediately. --}}
                <div class="reg-alert reg-alert-info">
                    <strong>Approval is required.</strong> Your account will not work until an
                    administrator approves it. You will not be able to sign in straight after
                    registering.
                </div>

                @if ($errors->any())
                <div class="reg-alert reg-alert-bad">
                    Please check the highlighted fields below.
                </div>
                @endif

                <p class="reg-section">Organisation</p>
                <div class="reg-grid">
                    <div class="reg-field">
                        <label for="name">Organisation name <span class="req">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                               class="{{ $errors->has('name') ? 'has-error' : '' }}"
                               placeholder="e.g. Politeknik Sultan Idris Shah" required>
                        @error('name')<span class="reg-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="reg-field">
                        <label for="organization_type">Type</label>
                        <select id="organization_type" name="organization_type"
                                class="{{ $errors->has('organization_type') ? 'has-error' : '' }}">
                            <option value="">Select type</option>
                            {{-- Same three values the Add Client wizard writes. --}}
                            <option value="gov" @selected(old('organization_type') === 'gov')>Government</option>
                            <option value="ngo" @selected(old('organization_type') === 'ngo')>NGO</option>
                            <option value="company" @selected(old('organization_type') === 'company')>Company</option>
                        </select>
                        @error('organization_type')<span class="reg-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="reg-grid reg-grid-1">
                    <div class="reg-field">
                        <label for="address_1">Address line 1</label>
                        <input type="text" id="address_1" name="address_1" value="{{ old('address_1') }}"
                               class="{{ $errors->has('address_1') ? 'has-error' : '' }}" placeholder="Street address">
                        @error('address_1')<span class="reg-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="reg-grid reg-grid-1">
                    <div class="reg-field">
                        <label for="address_2">Address line 2</label>
                        <input type="text" id="address_2" name="address_2" value="{{ old('address_2') }}"
                               class="{{ $errors->has('address_2') ? 'has-error' : '' }}" placeholder="Optional">
                        @error('address_2')<span class="reg-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="reg-grid">
                    <div class="reg-field">
                        <label for="postcode">Postcode</label>
                        <input type="text" id="postcode" name="postcode" value="{{ old('postcode') }}" maxlength="10"
                               class="{{ $errors->has('postcode') ? 'has-error' : '' }}" placeholder="e.g. 50000">
                        @error('postcode')<span class="reg-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="reg-field">
                        <label for="district">District</label>
                        <input type="text" id="district" name="district" value="{{ old('district') }}"
                               class="{{ $errors->has('district') ? 'has-error' : '' }}" placeholder="Enter district">
                        @error('district')<span class="reg-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="reg-grid">
                    <div class="reg-field">
                        <label for="state">State</label>
                        <select id="state" name="state" class="{{ $errors->has('state') ? 'has-error' : '' }}">
                            <option value="">Select state</option>
                            @foreach ([
                                'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang',
                                'Perak', 'Perlis', 'Pulau Pinang', 'Sabah', 'Sarawak', 'Selangor',
                                'Terengganu', 'W.P. Kuala Lumpur', 'W.P. Labuan', 'W.P. Putrajaya',
                            ] as $state)
                                <option value="{{ $state }}" @selected(old('state') === $state)>{{ $state }}</option>
                            @endforeach
                        </select>
                        @error('state')<span class="reg-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="reg-field">
                        <label for="country">Country</label>
                        <input type="text" id="country" name="country" value="{{ old('country', 'Malaysia') }}"
                               class="{{ $errors->has('country') ? 'has-error' : '' }}">
                        @error('country')<span class="reg-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="reg-grid">
                    <div class="reg-field">
                        <label for="phone">Phone number <span class="req">*</span></label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                               class="{{ $errors->has('phone') ? 'has-error' : '' }}" placeholder="e.g. 03-1234 5678" required>
                        @error('phone')
                            <span class="reg-error">{{ $message }}</span>
                        @else
                            <span class="reg-hint">Required — we use this to verify your organisation.</span>
                        @enderror
                    </div>
                    <div class="reg-field">
                        <label for="website">Website</label>
                        <input type="url" id="website" name="website" value="{{ old('website') }}"
                               class="{{ $errors->has('website') ? 'has-error' : '' }}" placeholder="https://example.com">
                        @error('website')<span class="reg-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="reg-grid">
                    <div class="reg-field">
                        <label for="contact_person">Contact person <span class="req">*</span></label>
                        <input type="text" id="contact_person" name="contact_person" value="{{ old('contact_person') }}"
                               class="{{ $errors->has('contact_person') ? 'has-error' : '' }}" placeholder="Full name" required>
                        @error('contact_person')<span class="reg-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="reg-field">
                        <label for="client_email">Organisation email</label>
                        <input type="email" id="client_email" name="client_email" value="{{ old('client_email') }}"
                               class="{{ $errors->has('client_email') ? 'has-error' : '' }}" placeholder="office@example.com">
                        @error('client_email')
                            <span class="reg-error">{{ $message }}</span>
                        @else
                            <span class="reg-hint">Leave blank to reuse your login email.</span>
                        @enderror
                    </div>
                </div>

                <hr class="reg-rule">

                <p class="reg-section">Your login</p>
                <div class="reg-grid reg-grid-1">
                    <div class="reg-field">
                        <label for="email">Email <span class="req">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               class="{{ $errors->has('email') ? 'has-error' : '' }}"
                               autocomplete="username" placeholder="you@example.com" required>
                        @error('email')<span class="reg-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="reg-grid">
                    <div class="reg-field">
                        <label for="password">Password <span class="req">*</span></label>
                        <input type="password" id="password" name="password"
                               class="{{ $errors->has('password') ? 'has-error' : '' }}"
                               autocomplete="new-password" required>
                        @error('password')
                            <span class="reg-error">{{ $message }}</span>
                        @else
                            {{-- The real minimum comes from Settings → Configuration,
                                 the same source the validator reads, so this cannot
                                 promise a length the server will reject. --}}
                            <span class="reg-hint">At least {{ (int) \App\Models\SystemSetting::getValue('security', 'password_min_length', 8) }} characters.</span>
                        @enderror
                    </div>
                    <div class="reg-field">
                        <label for="password_confirmation">Confirm password <span class="req">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               autocomplete="new-password" required>
                    </div>
                </div>
            </div>

            <div class="reg-foot">
                <p class="reg-note">Already registered? <a href="{{ route('login') }}">Sign in</a></p>
                <button type="submit" class="reg-submit">
                    <span class="material-symbols-outlined" style="font-size: 16px;">how_to_reg</span>
                    SUBMIT REGISTRATION
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
