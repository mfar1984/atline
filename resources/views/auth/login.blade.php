<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ \App\Models\SystemSetting::systemName() }}</title>
    <link rel="icon" type="image/x-icon" href="{{ \App\Models\SystemSetting::iconPath() }}">
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
            background: url('{{ \App\Models\SystemSetting::heroImagePath() }}') center center / cover no-repeat;
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
        
        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.95);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            backdrop-filter: blur(5px);
        }
        
        .loading-overlay.active {
            display: flex;
        }
        
        .loading-content {
            text-align: center;
        }
        
        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid #e5e7eb;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .loading-text {
            font-size: 14px;
            color: #374151;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .loading-subtext {
            font-size: 12px;
            color: #9ca3af;
        }
        
        .loading-dots {
            display: inline-block;
        }
        
        .loading-dots::after {
            content: '';
            animation: dots 1.5s steps(4, end) infinite;
        }
        
        @keyframes dots {
            0%, 20% { content: ''; }
            40% { content: '.'; }
            60% { content: '..'; }
            80%, 100% { content: '...'; }
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
    <!-- Floating Contact Widget -->
    @php
        $helplineSettings = \App\Models\SystemSetting::getGroup('helpline');
        $widgetEnabled = $helplineSettings['widget_enabled'] ?? true;
        $whatsapp = $helplineSettings['whatsapp'] ?? '60166337231';
        $telegram = $helplineSettings['telegram'] ?? 'Fahmmie85';
        $email = $helplineSettings['email'] ?? 'help@atline.com.my';
    @endphp
    
    @if($widgetEnabled)
    <div class="floating-contact-widget">
        <!-- Main Button -->
        <button class="contact-main-btn" id="contactMainBtn" onclick="toggleContactMenu()">
            <span class="material-symbols-outlined contact-icon">support_agent</span>
            <span class="material-symbols-outlined close-icon">close</span>
        </button>
        
        <!-- Contact Options -->
        <div class="contact-options" id="contactOptions">
            @if($whatsapp)
            <a href="https://wa.me/{{ $whatsapp }}" target="_blank" class="contact-option whatsapp" data-tooltip="WhatsApp">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
            </a>
            @endif
            
            @if($telegram)
            <a href="https://t.me/{{ ltrim($telegram, '@') }}" target="_blank" class="contact-option telegram" data-tooltip="Telegram">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                </svg>
            </a>
            @endif
            
            @if($email)
            <a href="mailto:{{ $email }}" class="contact-option email" data-tooltip="Email">
                <span class="material-symbols-outlined">mail</span>
            </a>
            @endif
        </div>
    </div>
    @endif

    <style>
        /* Floating Contact Widget */
        .floating-contact-widget {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 9999;
        }
        
        .contact-main-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .contact-main-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.6);
        }
        
        .contact-main-btn .material-symbols-outlined {
            font-size: 28px;
            color: white;
            transition: all 0.3s ease;
            position: absolute;
        }
        
        .contact-main-btn .contact-icon {
            opacity: 1;
            transform: rotate(0deg);
        }
        
        .contact-main-btn .close-icon {
            opacity: 0;
            transform: rotate(180deg);
        }
        
        .contact-main-btn.active .contact-icon {
            opacity: 0;
            transform: rotate(-180deg);
        }
        
        .contact-main-btn.active .close-icon {
            opacity: 1;
            transform: rotate(0deg);
        }
        
        .contact-options {
            position: absolute;
            bottom: 75px;
            right: 5px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: all 0.3s ease;
            align-items: center;
        }
        
        .contact-options.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .contact-option {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            position: relative;
            transform: scale(0);
            animation: popIn 0.3s ease forwards;
        }
        
        .contact-option:nth-child(1) {
            animation-delay: 0.1s;
        }
        
        .contact-option:nth-child(2) {
            animation-delay: 0.15s;
        }
        
        .contact-option:nth-child(3) {
            animation-delay: 0.2s;
        }
        
        @keyframes popIn {
            0% {
                transform: scale(0) rotate(-180deg);
                opacity: 0;
            }
            70% {
                transform: scale(1.2) rotate(10deg);
            }
            100% {
                transform: scale(1) rotate(0deg);
                opacity: 1;
            }
        }
        
        .contact-option:hover {
            transform: scale(1.15);
        }
        
        .contact-option svg,
        .contact-option .material-symbols-outlined {
            width: 24px;
            height: 24px;
            font-size: 24px;
        }
        
        .contact-option.whatsapp {
            background: #25D366;
            color: white;
        }
        
        .contact-option.telegram {
            background: #0088cc;
            color: white;
        }
        
        .contact-option.email {
            background: #ea4335;
            color: white;
        }
        
        /* Tooltip */
        .contact-option::before {
            content: attr(data-tooltip);
            position: absolute;
            right: 60px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .contact-option::after {
            content: '';
            position: absolute;
            right: 52px;
            border: 6px solid transparent;
            border-left-color: rgba(0, 0, 0, 0.8);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .contact-option:hover::before,
        .contact-option:hover::after {
            opacity: 1;
            visibility: visible;
        }
        
        /* Pulse Animation */
        @keyframes pulse-widget {
            0%, 100% {
                box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
            }
            50% {
                box-shadow: 0 4px 20px rgba(59, 130, 246, 0.8), 0 0 0 10px rgba(59, 130, 246, 0.2);
            }
        }
        
        .contact-main-btn {
            animation: pulse-widget 2s ease-in-out infinite;
        }
        
        .contact-main-btn.active {
            animation: none;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .floating-contact-widget {
                bottom: 20px;
                right: 20px;
            }
            
            .contact-main-btn {
                width: 55px;
                height: 55px;
            }
            
            .contact-option {
                width: 45px;
                height: 45px;
            }
        }
    </style>

    <script>
        function toggleContactMenu() {
            const mainBtn = document.getElementById('contactMainBtn');
            const options = document.getElementById('contactOptions');
            
            mainBtn.classList.toggle('active');
            options.classList.toggle('active');
        }
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const widget = document.querySelector('.floating-contact-widget');
            if (!widget.contains(event.target)) {
                document.getElementById('contactMainBtn').classList.remove('active');
                document.getElementById('contactOptions').classList.remove('active');
            }
        });
    </script>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <div class="loading-text">Signing you in<span class="loading-dots"></span></div>
            <div class="loading-subtext">Please wait a moment</div>
        </div>
    </div>

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
                <img src="{{ \App\Models\SystemSetting::logoPath() }}" alt="Logo" class="login-logo">
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
        
        // Login form loading
        const loginForm = document.querySelector('form[action="{{ route("login") }}"]');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                // Show loading overlay
                document.getElementById('loadingOverlay').classList.add('active');
                
                // Disable submit button to prevent double submission
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.6';
                    submitBtn.style.cursor = 'not-allowed';
                }
            });
        }
    </script>
</body>
</html>
