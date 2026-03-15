<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - {{ \App\Models\SystemSetting::systemName() }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center" style="font-family: 'Poppins', sans-serif;">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 text-center">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-outlined text-blue-600" style="font-size: 24px;">security</span>
                </div>
                <h1 class="text-gray-900" style="font-size: 14px; font-weight: 600;">Two-Factor Authentication</h1>
                <p class="text-gray-500 mt-1" style="font-size: 11px;">Enter the code from your authenticator app</p>
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('2fa.verify') }}" class="p-6">
                @csrf
                
                @if($errors->any())
                <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded" style="font-size: 11px;">
                    {{ $errors->first() }}
                </div>
                @endif

                <div class="mb-6">
                    <label class="block text-gray-700 mb-2" style="font-size: 11px;">Authentication Code</label>
                    <input type="text" name="code" maxlength="6" pattern="[0-9A-Za-z]{6,}" required autofocus
                           class="w-full px-4 py-3 border border-gray-300 rounded text-center tracking-widest focus:outline-none focus:border-blue-500"
                           style="font-size: 18px; font-family: monospace; letter-spacing: 0.5em;"
                           placeholder="000000"
                           autocomplete="one-time-code">
                    <p class="text-gray-400 mt-2 text-center" style="font-size: 11px;">
                        Enter the 6-digit code from your authenticator app or a recovery code
                    </p>
                </div>

                <button type="submit" class="w-full px-4 py-3 bg-blue-600 text-white font-medium rounded hover:bg-blue-700 transition" style="font-size: 11px;">
                    VERIFY
                </button>

                <div class="mt-4 text-center">
                    <a href="{{ route('login') }}" class="text-gray-500 hover:text-blue-600 transition" style="font-size: 11px;">
                        ← Back to Login
                    </a>
                </div>
            </form>
        </div>

        <p class="text-center text-gray-400 mt-4" style="font-size: 10px;">
            © {{ date('Y') }} {{ \App\Models\SystemSetting::companyName() }}
        </p>
    </div>
</body>
</html>
