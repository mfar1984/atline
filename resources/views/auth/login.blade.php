<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ \App\Models\SystemSetting::systemName() }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
        }
        
        .login-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }
        
        /* Left Side - Hero Background (75%) */
        .hero-section {
            width: 75%;
            background: url('/images/hero-bg.png') center center / cover no-repeat;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .hero-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.1);
        }
        
        /* Vertical Separator */
        .separator {
            width: 3px;
            background: linear-gradient(to bottom, transparent 0%, #e5e7eb 10%, #3b82f6 50%, #e5e7eb 90%, transparent 100%);
            position: relative;
        }
        
        .separator::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 24px;
            height: 24px;
            background: #3b82f6;
            border-radius: 50%;
            box-shadow: 0 0 0 8px rgba(59, 130, 246, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }
        
        .separator::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 24px;
            height: 24px;
            background: transparent;
            border-radius: 50%;
            animation: pulse-ring 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: translate(-50%, -50%) scale(1);
                box-shadow: 0 0 0 8px rgba(59, 130, 246, 0.3);
            }
            50% {
                transform: translate(-50%, -50%) scale(1.2);
                box-shadow: 0 0 0 14px rgba(59, 130, 246, 0.15);
            }
        }
        
        @keyframes pulse-ring {
            0% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.5);
            }
            50% {
                box-shadow: 0 0 0 30px rgba(59, 130, 246, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
            }
        }
        
        /* Right Side - Login Form (25%) */
        .login-section {
            width: 25%;
            min-width: 380px;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .login-logo {
            width: 160px;
            height: auto;
        }
        
        .login-form {
            width: 100%;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-label {
            display: block;
            font-size: 0.6875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            transition: all 0.2s ease;
            outline: none;
        }
        
        .form-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-input::placeholder {
            color: #9ca3af;
        }
        
        .form-input.error {
            border-color: #ef4444;
        }
        
        .input-icon-wrapper {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 18px;
        }
        
        .input-icon-wrapper .form-input {
            padding-left: 2.5rem;
        }
        
        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        
        .remember-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }
        
        .remember-checkbox input {
            width: 1rem;
            height: 1rem;
            accent-color: #3b82f6;
        }
        
        .remember-checkbox span {
            font-size: 0.6875rem;
            color: #6b7280;
        }
        
        .login-btn {
            width: 100%;
            padding: 0.875rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .login-btn:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
            transform: translateY(-1px);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.6875rem;
            margin-bottom: 1.25rem;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .login-footer p {
            font-size: 0.625rem;
            color: #9ca3af;
        }
        
        .legal-links {
            margin-top: 0.75rem;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
        }
        
        .legal-links a {
            font-size: 0.625rem;
            color: #6b7280;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        .legal-links a:hover {
            color: #3b82f6;
        }
        
        .legal-links span {
            font-size: 0.625rem;
            color: #d1d5db;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .hero-section {
                width: 60%;
            }
            .login-section {
                width: 40%;
                min-width: 320px;
            }
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }
            .hero-section {
                width: 100%;
                min-height: 200px;
            }
            .separator {
                width: 100%;
                height: 1px;
                background: linear-gradient(to right, transparent 0%, #e5e7eb 10%, #3b82f6 50%, #e5e7eb 90%, transparent 100%);
            }
            .separator::before {
                display: none;
            }
            .login-section {
                width: 100%;
                min-width: unset;
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Side - Hero Section (75%) -->
        <div class="hero-section">
            <div class="hero-overlay"></div>
        </div>
        
        <!-- Vertical Separator -->
        <div class="separator"></div>
        
        <!-- Right Side - Login Form (25%) -->
        <div class="login-section">
            <div class="login-header">
                <img src="/images/logo.png" alt="Logo" class="login-logo">
            </div>
            
            @if($errors->any())
            <div class="error-message">
                {{ $errors->first() }}
            </div>
            @endif
            
            <form method="POST" action="{{ route('login') }}" class="login-form">
                @csrf
                
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <div class="input-icon-wrapper">
                        <span class="material-symbols-outlined input-icon">mail</span>
                        <input type="email" name="email" value="{{ old('email') }}" 
                               class="form-input {{ $errors->has('email') ? 'error' : '' }}"
                               placeholder="Enter your email" required autofocus>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-icon-wrapper">
                        <span class="material-symbols-outlined input-icon">lock</span>
                        <input type="password" name="password" 
                               class="form-input {{ $errors->has('password') ? 'error' : '' }}"
                               placeholder="Enter your password" required>
                    </div>
                </div>
                
                <div class="remember-row">
                    <label class="remember-checkbox">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span>Remember me</span>
                    </label>
                </div>
                
                <button type="submit" class="login-btn">
                    Sign In
                </button>
            </form>
            
            <div class="login-footer">
                <p>© {{ date('Y') }} {{ \App\Models\SystemSetting::companyName() }}. All rights reserved.</p>
                <div class="legal-links">
                    <a href="javascript:void(0)" onclick="openLegalModal('legal-modal', 'privacy')">Privacy</a>
                    <span>/</span>
                    <a href="javascript:void(0)" onclick="openLegalModal('legal-modal', 'terms')">Terms</a>
                    <span>/</span>
                    <a href="javascript:void(0)" onclick="openLegalModal('legal-modal', 'disclaimer')">Disclaimer</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Legal Modal -->
    <x-modals.legal-modal id="legal-modal" />

    <!-- Hidden Content Templates - Privacy (Multi-language) -->
    <template id="privacy-content-template-en">
        <div style="font-family: Poppins, sans-serif; font-size: 12px; color: #4b5563; line-height: 1.7;">
            @include('components.legal.privacy-content-en')
        </div>
    </template>
    <template id="privacy-content-template-ms">
        <div style="font-family: Poppins, sans-serif; font-size: 12px; color: #4b5563; line-height: 1.7;">
            @include('components.legal.privacy-content-ms')
        </div>
    </template>
    <template id="privacy-content-template-zh">
        <div style="font-family: Poppins, sans-serif; font-size: 12px; color: #4b5563; line-height: 1.7;">
            @include('components.legal.privacy-content-zh')
        </div>
    </template>

    <!-- Hidden Content Templates - Terms (Multi-language) -->
    <template id="terms-content-template-en">
        <div style="font-family: Poppins, sans-serif; font-size: 12px; color: #4b5563; line-height: 1.7;">
            @include('components.legal.terms-content-en')
        </div>
    </template>
    <template id="terms-content-template-ms">
        <div style="font-family: Poppins, sans-serif; font-size: 12px; color: #4b5563; line-height: 1.7;">
            @include('components.legal.terms-content-ms')
        </div>
    </template>
    <template id="terms-content-template-zh">
        <div style="font-family: Poppins, sans-serif; font-size: 12px; color: #4b5563; line-height: 1.7;">
            @include('components.legal.terms-content-zh')
        </div>
    </template>

    <!-- Hidden Content Templates - Disclaimer (Multi-language) -->
    <template id="disclaimer-content-template-en">
        <div style="font-family: Poppins, sans-serif; font-size: 12px; color: #4b5563; line-height: 1.7;">
            @include('components.legal.disclaimer-content-en')
        </div>
    </template>
    <template id="disclaimer-content-template-ms">
        <div style="font-family: Poppins, sans-serif; font-size: 12px; color: #4b5563; line-height: 1.7;">
            @include('components.legal.disclaimer-content-ms')
        </div>
    </template>
    <template id="disclaimer-content-template-zh">
        <div style="font-family: Poppins, sans-serif; font-size: 12px; color: #4b5563; line-height: 1.7;">
            @include('components.legal.disclaimer-content-zh')
        </div>
    </template>

    <!-- Legal Modal Scripts -->
    <script>
        const legalConfig = {
            privacy: { 
                title: 'Privacy Policy', 
                icon: 'security', 
                hasLanguages: true,
                languages: {
                    en: { label: 'English', templateId: 'privacy-content-template-en' },
                    ms: { label: 'Bahasa Melayu', templateId: 'privacy-content-template-ms' },
                    zh: { label: '中文', templateId: 'privacy-content-template-zh' }
                },
                defaultLang: 'en'
            },
            terms: { 
                title: 'Terms of Service', 
                icon: 'description', 
                hasLanguages: true,
                languages: {
                    en: { label: 'English', templateId: 'terms-content-template-en' },
                    ms: { label: 'Bahasa Melayu', templateId: 'terms-content-template-ms' },
                    zh: { label: '中文', templateId: 'terms-content-template-zh' }
                },
                defaultLang: 'en'
            },
            disclaimer: { 
                title: 'Disclaimer', 
                icon: 'info', 
                hasLanguages: true,
                languages: {
                    en: { label: 'English', templateId: 'disclaimer-content-template-en' },
                    ms: { label: 'Bahasa Melayu', templateId: 'disclaimer-content-template-ms' },
                    zh: { label: '中文', templateId: 'disclaimer-content-template-zh' }
                },
                defaultLang: 'en'
            }
        };

        let currentLegalType = null;
        let currentLang = 'en';

        function openLegalModal(modalId, type) {
            const modal = document.getElementById(modalId);
            const config = legalConfig[type];
            currentLegalType = type;
            
            if (modal && config) {
                const titleEl = document.getElementById(modalId + '-title');
                if (titleEl) {
                    titleEl.textContent = config.title;
                    const iconEl = titleEl.previousElementSibling;
                    if (iconEl) iconEl.textContent = config.icon;
                }
                
                const langSwitcher = document.getElementById(modalId + '-lang-switcher');
                if (config.hasLanguages) {
                    if (langSwitcher) {
                        langSwitcher.style.display = 'flex';
                        currentLang = config.defaultLang;
                        updateLanguageButtons(modalId, currentLang);
                    }
                    loadLegalContent(modalId, config.languages[currentLang].templateId);
                } else {
                    if (langSwitcher) langSwitcher.style.display = 'none';
                    loadLegalContent(modalId, config.templateId);
                }
                
                modal.style.display = 'flex';
            }
        }

        function loadLegalContent(modalId, templateId) {
            const contentEl = document.getElementById(modalId + '-content');
            const template = document.getElementById(templateId);
            if (contentEl && template) {
                contentEl.innerHTML = template.innerHTML;
            }
        }

        function switchLegalLanguage(modalId, lang) {
            const config = legalConfig[currentLegalType];
            if (config && config.hasLanguages && config.languages[lang]) {
                currentLang = lang;
                loadLegalContent(modalId, config.languages[lang].templateId);
                updateLanguageButtons(modalId, lang);
            }
        }

        function updateLanguageButtons(modalId, activeLang) {
            const buttons = document.querySelectorAll('#' + modalId + '-lang-switcher button');
            buttons.forEach(btn => {
                if (btn.dataset.lang === activeLang) {
                    btn.style.backgroundColor = '#3b82f6';
                    btn.style.color = 'white';
                } else {
                    btn.style.backgroundColor = '#f3f4f6';
                    btn.style.color = '#374151';
                }
            });
        }

        function closeLegalModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) modal.style.display = 'none';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeLegalModal('legal-modal');
        });
    </script>
</body>
</html>
