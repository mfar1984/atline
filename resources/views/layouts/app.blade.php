<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $systemName = \App\Models\SystemSetting::systemName();
        @endphp
        <title>@yield('title', 'Dashboard') - {{ $systemName }}</title>
        <link rel="icon" type="image/x-icon" href="{{ \App\Models\SystemSetting::iconPath() }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

        <!-- Vite CSS -->
        @vite(['resources/css/app.css'])

        <!-- Alpine.js -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <style>
            /* Base Styles */
            body {
                font-family: 'Poppins', sans-serif;
                font-size: 14px;
                line-height: 1.5;
            }

            [x-cloak] { display: none !important; }

            /* Sidebar Styles */
            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                height: 100%;
                background-color: white;
                color: #1f2937;
                transition: all 0.3s ease;
                z-index: 30;
                border-right: 1px solid #e5e7eb;
            }

            .sidebar-expanded { width: 256px; }
            .sidebar-collapsed { width: 64px; }

            .sidebar-collapsed .sidebar-nav-item {
                justify-content: center;
                padding-left: 0;
                padding-right: 0;
            }

            .sidebar-collapsed .sidebar-nav-icon {
                margin-left: auto;
                margin-right: auto;
            }

            .sidebar-collapsed .submenu-dropdown {
                position: absolute;
                left: 64px;
                top: 0;
                background-color: white;
                border: 1px solid #e5e7eb;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                border-radius: 4px;
                min-width: 200px;
                max-height: 80vh;
                overflow-y: auto;
                z-index: 40;
            }

            .sidebar-header {
                padding: 1.5rem 1rem;
                border-bottom: 1px solid #e5e7eb;
                height: 82px;
                box-sizing: border-box;
            }

            .sidebar-toggle {
                padding: 0.5rem;
                border-radius: 4px;
                color: #6b7280;
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s ease;
            }

            .sidebar-toggle:hover {
                color: #1f2937;
                background-color: #f3f4f6;
            }

            .sidebar-nav { padding: 0; }

            .sidebar-nav-item {
                display: flex;
                align-items: center;
                padding: 0.5rem 1rem;
                font-size: 12px;
                font-weight: 500;
                transition: all 0.15s ease;
                border-radius: 0;
                margin: 0;
            }

            .sidebar-nav-item-active {
                color: #1f2937;
                background-color: #dbeafe !important;
                position: relative;
            }

            .sidebar-nav-item-active::after {
                content: '';
                position: absolute;
                right: 0;
                top: 0;
                bottom: 0;
                width: 3px;
                background-color: #3b82f6;
            }

            .sidebar-nav-item-inactive {
                color: #4b5563;
                position: relative;
                transition: all 0.2s ease;
            }

            .sidebar-nav-item-inactive:hover {
                background-color: #f0f9ff !important;
                color: #3b82f6 !important;
            }

            .sidebar-nav-item-inactive:hover .sidebar-nav-icon {
                color: #3b82f6 !important;
            }

            .sidebar-nav-item-inactive:hover::after {
                content: '';
                position: absolute;
                right: 0;
                top: 0;
                bottom: 0;
                width: 3px;
                background-color: #93c5fd;
            }

            .sidebar-nav-item-parent-active {
                color: #1f2937;
                background-color: transparent !important;
                position: relative;
            }

            .sidebar-nav-item-parent-active:hover {
                background-color: #f0f9ff !important;
                color: #3b82f6 !important;
            }

            .sidebar-nav-icon {
                margin-right: 0.75rem;
                width: 20px;
                height: 20px;
                flex-shrink: 0;
            }

            /* Submenu tree lines */
            .submenu-container {
                position: relative;
                margin-left: 24px;
                margin-top: 4px;
            }

            .submenu-container::before {
                content: '';
                position: absolute;
                left: -12px;
                top: 0;
                bottom: 0;
                width: 1px;
                background-color: #d1d5db;
            }

            .submenu-item {
                position: relative;
                padding-left: 20px;
                z-index: 1;
                font-size: 11px !important;
            }

            .submenu-item::before {
                content: '';
                position: absolute;
                left: -12px;
                top: 50%;
                width: 18px;
                height: 1px;
                background-color: #d1d5db;
            }

            .submenu-item::after {
                content: '';
                position: absolute;
                left: 0;
                top: 50%;
                transform: translateY(-50%);
                width: 6px;
                height: 6px;
                background-color: #9ca3af;
                border-radius: 50%;
            }

            .submenu-item.sidebar-nav-item-inactive:hover::after,
            .submenu-item.sidebar-nav-item-active::after {
                display: none;
            }

            /* Header Styles */
            .header {
                background-color: white;
                border-bottom: 1px solid #e5e7eb;
                position: sticky;
                top: 0;
                z-index: 1000;
            }

            .topbar {
                background-color: #f9fafb;
                border-bottom: 1px solid #e5e7eb;
            }

            .topbar-container {
                padding: 0 1.5rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                height: 48px;
            }

            .topbar-left {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                color: #4b5563;
                font-size: 11px;
            }

            .welcome-text { font-weight: 500; color: #374151; }
            .topbar-separator { color: #9ca3af; }
            .topbar-right { display: flex; align-items: center; gap: 0.75rem; }

            .topbar-user-btn {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                font-size: 11px;
                color: #374151;
                padding: 0.25rem 0.5rem;
                border-radius: 4px;
                transition: color 0.2s ease;
            }

            .topbar-user-btn:hover { color: #3b82f6; }

            .user-avatar {
                width: 24px;
                height: 24px;
                background-color: #3b82f6;
                color: white;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .topbar-dropdown {
                position: absolute;
                right: 0;
                margin-top: 0.5rem;
                width: 192px;
                background-color: white;
                border-radius: 6px;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                border: 1px solid #e5e7eb;
                padding: 0.25rem 0;
                z-index: 50;
            }

            .topbar-dropdown-item {
                display: flex;
                align-items: center;
                padding: 0.5rem 0.75rem;
                font-size: 11px;
                color: #374151;
                transition: background-color 0.2s ease;
                gap: 8px;
            }

            .topbar-dropdown-item:hover { background-color: #f9fafb; }
            .topbar-dropdown-item svg { margin-right: 12px; }

            /* Breadcrumb Styles */
            .breadcrumb-bar { background-color: white; }

            .breadcrumb-container {
                padding: 0 1.5rem;
                display: flex;
                align-items: center;
                height: 32px;
            }

            /* Footer Styles */
            .footer {
                background-color: white;
                border-top: 1px solid #e5e7eb;
                margin-top: auto;
            }

            .footer-container { padding: 0 1.5rem; }

            .footer-content {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0.5rem 0;
                min-height: 32px;
            }

            .footer-copyright { color: #4b5563; font-size: 11px; }
            .footer-links { display: flex; align-items: center; gap: 0.5rem; }

            .footer-link {
                color: #4b5563;
                font-size: 11px;
                text-decoration: none;
                transition: color 0.2s ease;
            }

            .footer-link:hover { color: #3b82f6; }
            .footer-separator { color: #9ca3af; font-size: 11px; margin: 0 4px; }

            /* Main Content */
            .main-content { transition: margin-left 0.3s ease; }
            .main-content-expanded { margin-left: 256px; }
            .main-content-collapsed { margin-left: 64px; }

            /* Separator */
            .sidebar-separator { margin: 0.75rem; padding: 0 0.75rem; }
            .sidebar-separator-line { border: 0; border-top: 1px solid #e5e7eb; height: 1px; }

            /* ============================================
               MOBILE & TABLET RESPONSIVE STYLES
               Desktop CSS is NOT modified - only mobile/tablet
               ============================================ */

            /* Tablet Portrait (768px - 1024px) */
            @media (min-width: 768px) and (max-width: 1024px) and (orientation: portrait) {
                .sidebar {
                    transform: translateX(-100%);
                    width: 280px !important;
                    z-index: 9001 !important;
                }
                .sidebar.sidebar-mobile-open { transform: translateX(0) !important; }
                .sidebar-expanded, .sidebar-collapsed { width: 280px !important; }
                .sidebar-toggle { display: none !important; }
                .main-content-expanded, .main-content-collapsed { margin-left: 0 !important; }
                .sidebar-overlay {
                    position: fixed;
                    inset: 0;
                    background-color: rgba(75, 85, 99, 0.75);
                    z-index: 9000 !important;
                }
                
                /* Topbar adjustments for tablet portrait */
                .topbar-container { 
                    padding-left: 1rem; 
                    padding-right: 1rem; 
                    height: 52px;
                }
                .welcome-text { display: none !important; }
                .topbar-separator { display: none !important; }
                .current-date { font-size: 11px !important; }
                .topbar-user-btn .user-email { 
                    max-width: 150px;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }
                
                /* Breadcrumb adjustments */
                .breadcrumb-container { 
                    padding-left: 1rem; 
                    padding-right: 1rem;
                    height: 36px;
                }
                
                /* Footer adjustments */
                .footer-content {
                    flex-direction: row;
                    padding: 0.75rem 0;
                }
                .footer-container { padding: 0 1rem; }
                
                /* Main content padding */
                main.p-6 { padding: 1rem !important; }
                
                /* Mobile menu button visible */
                .mobile-menu-btn { display: flex !important; }
            }

            /* Tablet Landscape (1024px - 1280px) */
            @media (min-width: 1024px) and (max-width: 1280px) and (orientation: landscape) {
                /* Sidebar stays visible but narrower */
                .sidebar-expanded { width: 220px !important; }
                .main-content-expanded { margin-left: 220px !important; }
                
                /* Topbar adjustments */
                .topbar-container { padding-left: 1.25rem; padding-right: 1.25rem; }
                .welcome-text { font-size: 10px !important; }
                .current-date { font-size: 10px !important; }
                .topbar-user-btn .user-email { 
                    max-width: 120px;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                    font-size: 10px !important;
                }
                
                /* Sidebar nav items smaller */
                .sidebar-nav-item { 
                    padding: 0.4rem 0.75rem !important; 
                    font-size: 11px !important;
                }
                .sidebar-nav-icon { 
                    width: 18px !important; 
                    height: 18px !important; 
                    margin-right: 0.5rem !important;
                }
                .submenu-item { font-size: 10px !important; }
                
                /* Main content padding */
                main.p-6 { padding: 1.25rem !important; }
            }

            /* Mobile Portrait (up to 480px) */
            @media (max-width: 480px) {
                .sidebar {
                    transform: translateX(-100%);
                    width: 100% !important;
                    max-width: 300px;
                    z-index: 9001 !important;
                }
                .sidebar.sidebar-mobile-open { transform: translateX(0) !important; }
                .sidebar-expanded, .sidebar-collapsed { width: 100% !important; max-width: 300px; }
                .sidebar-toggle { display: none !important; }
                .main-content-expanded, .main-content-collapsed { margin-left: 0 !important; }
                .sidebar-overlay {
                    position: fixed;
                    inset: 0;
                    background-color: rgba(75, 85, 99, 0.8);
                    z-index: 9000 !important;
                }
                
                /* Topbar - very compact */
                .topbar-container { 
                    padding-left: 0.5rem; 
                    padding-right: 0.5rem;
                    height: 44px;
                }
                .welcome-text, .topbar-separator { display: none !important; }
                .current-date { 
                    font-size: 9px !important;
                    max-width: 140px;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }
                .topbar-user-btn { padding: 0.25rem !important; gap: 0.5rem !important; }
                .topbar-user-btn .user-email { display: none !important; }
                .user-avatar { width: 28px !important; height: 28px !important; }
                .topbar-dropdown { 
                    width: calc(100vw - 1rem) !important; 
                    max-width: 280px;
                    right: 0.5rem !important;
                }
                
                /* Breadcrumb - compact */
                .breadcrumb-container { 
                    padding-left: 0.5rem; 
                    padding-right: 0.5rem;
                    height: 36px;
                    gap: 0.25rem;
                }
                .breadcrumb-container nav { font-size: 10px !important; }
                .breadcrumb-container nav .material-symbols-outlined { font-size: 14px !important; }
                
                /* Footer - stacked on mobile */
                .footer-content {
                    flex-direction: column;
                    gap: 0.5rem;
                    padding: 0.75rem 0;
                    text-align: center;
                }
                .footer-container { padding: 0 0.5rem; }
                .footer-copyright { font-size: 10px !important; }
                .footer-link { font-size: 10px !important; }
                .footer-separator { font-size: 10px !important; }
                
                /* Main content - minimal padding */
                main.p-6 { padding: 0.75rem !important; }
                
                /* Sidebar header */
                .sidebar-header { 
                    padding: 1rem 0.75rem !important; 
                    height: 70px !important;
                }
            }

            /* Mobile Landscape (481px - 767px) */
            @media (min-width: 481px) and (max-width: 767px) {
                .sidebar {
                    transform: translateX(-100%);
                    width: 280px !important;
                    z-index: 9001 !important;
                }
                .sidebar.sidebar-mobile-open { transform: translateX(0) !important; }
                .sidebar-expanded, .sidebar-collapsed { width: 280px !important; }
                .sidebar-toggle { display: none !important; }
                .main-content-expanded, .main-content-collapsed { margin-left: 0 !important; }
                .sidebar-overlay {
                    position: fixed;
                    inset: 0;
                    background-color: rgba(75, 85, 99, 0.75);
                    z-index: 9000 !important;
                }
                
                /* Topbar */
                .topbar-container { 
                    padding-left: 0.75rem; 
                    padding-right: 0.75rem;
                    height: 46px;
                }
                .welcome-text, .topbar-separator { display: none !important; }
                .current-date { font-size: 10px !important; }
                .topbar-user-btn .user-email { display: none !important; }
                .user-avatar { width: 26px !important; height: 26px !important; }
                
                /* Breadcrumb */
                .breadcrumb-container { 
                    padding-left: 0.75rem; 
                    padding-right: 0.75rem;
                    height: 34px;
                }
                
                /* Footer */
                .footer-content {
                    flex-direction: row;
                    padding: 0.5rem 0;
                }
                .footer-container { padding: 0 0.75rem; }
                .footer-copyright { font-size: 10px !important; }
                .footer-link { font-size: 10px !important; }
                
                /* Main content */
                main.p-6 { padding: 1rem !important; }
            }

            /* Generic Mobile (max-width: 768px) - Fallback */
            @media (max-width: 768px) {
                .sidebar {
                    transform: translateX(-100%);
                    width: 280px !important;
                    z-index: 9001 !important;
                }
                .sidebar.sidebar-mobile-open { transform: translateX(0) !important; }
                .sidebar-expanded, .sidebar-collapsed { width: 280px !important; }
                .sidebar-toggle { display: none !important; }
                .main-content-expanded, .main-content-collapsed { margin-left: 0 !important; }
                .sidebar-overlay {
                    position: fixed;
                    inset: 0;
                    background-color: rgba(75, 85, 99, 0.75);
                    z-index: 9000 !important;
                }
                .welcome-text, .topbar-separator { display: none !important; }
                .topbar-container { padding-left: 0.75rem; padding-right: 0.75rem; }
                .current-date { font-size: 10px !important; }
                .topbar-user-btn .user-email { display: none !important; }
            }

            /* Touch-friendly improvements for mobile/tablet */
            @media (max-width: 1024px) {
                /* Larger touch targets */
                .sidebar-nav-item {
                    min-height: 44px;
                }
                .submenu-item {
                    min-height: 40px;
                }
                .topbar-dropdown-item {
                    min-height: 44px;
                    padding: 0.75rem 1rem !important;
                }
                
                /* Better scrolling in sidebar */
                .sidebar-nav {
                    -webkit-overflow-scrolling: touch;
                }
                
                /* Prevent text selection on touch */
                .sidebar-nav-item,
                .topbar-user-btn {
                    -webkit-user-select: none;
                    user-select: none;
                }
            }

            /* Safe area insets for notched devices */
            @supports (padding: max(0px)) {
                @media (max-width: 768px) {
                    .topbar-container {
                        padding-left: max(0.75rem, env(safe-area-inset-left));
                        padding-right: max(0.75rem, env(safe-area-inset-right));
                    }
                    .breadcrumb-container {
                        padding-left: max(0.75rem, env(safe-area-inset-left));
                        padding-right: max(0.75rem, env(safe-area-inset-right));
                    }
                    .footer-container {
                        padding-left: max(0.75rem, env(safe-area-inset-left));
                        padding-right: max(0.75rem, env(safe-area-inset-right));
                        padding-bottom: max(0.5rem, env(safe-area-inset-bottom));
                    }
                    main.p-6 {
                        padding-left: max(0.75rem, env(safe-area-inset-left)) !important;
                        padding-right: max(0.75rem, env(safe-area-inset-right)) !important;
                    }
                }
            }

            /* Sidebar close button - only visible on mobile/tablet */
            .sidebar-close-btn {
                display: none;
            }
            @media (max-width: 1024px) {
                .sidebar-close-btn {
                    display: flex !important;
                }
            }

            /* Sidebar overlay visibility control */
            .sidebar-overlay {
                display: none;
            }
            @media (max-width: 1024px) {
                .sidebar-overlay[style*="display: none"] {
                    display: none !important;
                }
            }
            @media (min-width: 1025px) {
                .sidebar-overlay {
                    display: none !important;
                }
            }

            /* ============================================
               DATA LIST / TABLE RESPONSIVE STYLES
               For mobile and tablet views
               ============================================ */

            /* Page header responsive */
            @media (max-width: 768px) {
                /* Page header - stack on mobile */
                .bg-white.border.border-gray-200 > .px-6.py-4.flex {
                    flex-direction: column !important;
                    gap: 12px !important;
                    padding: 12px 16px !important;
                }
                .bg-white.border.border-gray-200 > .px-6.py-4.flex > div:first-child h2 {
                    font-size: 14px !important;
                }
                .bg-white.border.border-gray-200 > .px-6.py-4.flex > div:first-child p {
                    font-size: 11px !important;
                }
                .bg-white.border.border-gray-200 > .px-6.py-4.flex > .flex.items-center.gap-2 {
                    width: 100% !important;
                    justify-content: flex-start !important;
                }

                /* Search/filter form - stack on mobile */
                .px-6.py-3 form.flex {
                    flex-wrap: wrap !important;
                    gap: 8px !important;
                    padding: 12px 16px !important;
                }
                .px-6.py-3 form.flex .flex-1 {
                    flex: 1 1 100% !important;
                    min-width: 100% !important;
                }
                .px-6.py-3 form.flex select {
                    flex: 1 1 calc(50% - 4px) !important;
                    min-width: calc(50% - 4px) !important;
                }
                .px-6.py-3 form.flex button {
                    flex: 1 1 calc(50% - 4px) !important;
                }

                /* Data table container */
                .px-6 > .overflow-x-auto {
                    margin: 0 -16px !important;
                    padding: 0 !important;
                    border-left: none !important;
                    border-right: none !important;
                }

                /* Table cells - smaller padding */
                table td, table th {
                    padding: 8px 12px !important;
                    font-size: 11px !important;
                }
                table td:first-child, table th:first-child {
                    padding-left: 16px !important;
                }
                table td:last-child, table th:last-child {
                    padding-right: 16px !important;
                }

                /* Action buttons - smaller */
                .inline-flex.items-center.bg-white.border.border-gray-300.rounded-full {
                    transform: scale(0.9);
                }

                /* Pagination - stack on mobile */
                .px-6.py-3 > .flex.items-center.justify-between {
                    flex-direction: column !important;
                    gap: 12px !important;
                    padding: 12px 16px !important;
                }
                .px-6.py-3 > .flex.items-center.justify-between > p {
                    text-align: center !important;
                }
                .px-6.py-3 > .flex.items-center.justify-between > nav {
                    flex-wrap: wrap !important;
                    justify-content: center !important;
                }

                /* Buttons - full width on mobile */
                .inline-flex.items-center.gap-2.px-3 {
                    min-height: 36px !important;
                    padding: 0 12px !important;
                }

                /* Alert messages */
                .px-6.pb-3 > .px-4.py-3 {
                    margin: 0 16px 12px 16px !important;
                }

                /* Tabs navigation - scrollable */
                nav.flex.px-6[aria-label="Tabs"] {
                    overflow-x: auto !important;
                    padding: 0 16px !important;
                    -webkit-overflow-scrolling: touch;
                    scrollbar-width: none;
                }
                nav.flex.px-6[aria-label="Tabs"]::-webkit-scrollbar {
                    display: none;
                }
                nav.flex.px-6[aria-label="Tabs"] a {
                    white-space: nowrap !important;
                    flex-shrink: 0 !important;
                }
            }

            /* Tablet Portrait (768px - 1024px) */
            @media (min-width: 768px) and (max-width: 1024px) {
                /* Page header */
                .bg-white.border.border-gray-200 > .px-6.py-4.flex {
                    padding: 14px 20px !important;
                }

                /* Search form */
                .px-6.py-3 form.flex {
                    flex-wrap: wrap !important;
                    gap: 8px !important;
                }
                .px-6.py-3 form.flex .flex-1 {
                    flex: 1 1 40% !important;
                    min-width: 200px !important;
                }
                .px-6.py-3 form.flex select {
                    flex: 0 0 auto !important;
                    min-width: 120px !important;
                }

                /* Table cells */
                table td, table th {
                    padding: 10px 14px !important;
                    font-size: 11px !important;
                }
            }

            /* Mobile Portrait - Very small screens (up to 480px) */
            @media (max-width: 480px) {
                /* Hide less important columns on very small screens */
                table th:nth-child(n+4):not(:last-child),
                table td:nth-child(n+4):not(:last-child) {
                    display: none !important;
                }

                /* Even smaller padding */
                table td, table th {
                    padding: 6px 8px !important;
                    font-size: 10px !important;
                }

                /* Buttons - icon only on very small screens */
                .inline-flex.items-center.gap-2.px-3 span:not(.material-symbols-outlined) {
                    display: none !important;
                }
                .inline-flex.items-center.gap-2.px-3 {
                    padding: 0 10px !important;
                    gap: 0 !important;
                }

                /* Pagination - minimal */
                .px-6.py-3 > .flex.items-center.justify-between > nav a {
                    width: 28px !important;
                    height: 28px !important;
                    font-size: 10px !important;
                }
            }

            /* ============================================
               DASHBOARD RESPONSIVE STYLES
               ============================================ */
            @media (max-width: 768px) {
                /* Dashboard stats grid - 2 columns on mobile */
                div[style*="grid-template-columns: repeat(4, 1fr)"] {
                    grid-template-columns: repeat(2, 1fr) !important;
                    gap: 12px !important;
                }
                /* Dashboard stat cards */
                div[style*="grid-template-columns: repeat(4, 1fr)"] > div {
                    padding: 14px !important;
                }
                div[style*="grid-template-columns: repeat(4, 1fr)"] > div p[style*="font-size: 32px"] {
                    font-size: 24px !important;
                }
                div[style*="grid-template-columns: repeat(4, 1fr)"] > div span[style*="font-size: 48px"] {
                    font-size: 32px !important;
                }

                /* Dashboard main content grid - stack on mobile */
                div[style*="grid-template-columns: 2fr 1fr"],
                div[style*="grid-template-columns: 1fr 1fr"] {
                    grid-template-columns: 1fr !important;
                    gap: 16px !important;
                }

                /* Dashboard section headers */
                div[style*="background-color: #f9fafb"] {
                    padding: 14px !important;
                }
                div[style*="background-color: #f9fafb"] h3 {
                    font-size: 13px !important;
                }

                /* Dashboard tables */
                div[style*="background-color: #f9fafb"] table td,
                div[style*="background-color: #f9fafb"] table th {
                    padding: 8px 0 !important;
                    font-size: 11px !important;
                }
            }

            @media (min-width: 481px) and (max-width: 768px) {
                /* Dashboard stats - still 2 columns but larger */
                div[style*="grid-template-columns: repeat(4, 1fr)"] {
                    grid-template-columns: repeat(2, 1fr) !important;
                }
            }

            @media (min-width: 768px) and (max-width: 1024px) {
                /* Dashboard stats - 2x2 grid on tablet */
                div[style*="grid-template-columns: repeat(4, 1fr)"] {
                    grid-template-columns: repeat(2, 1fr) !important;
                }
                /* Dashboard main content - stack on tablet portrait */
                div[style*="grid-template-columns: 2fr 1fr"] {
                    grid-template-columns: 1fr !important;
                }
            }

            /* ============================================
               MODAL RESPONSIVE STYLES
               ============================================ */
            @media (max-width: 768px) {
                /* Modals - full width on mobile */
                .delete-modal-content,
                div[style*="max-width: 450px"],
                div[style*="max-width: 400px"],
                div[style*="width: 480px"] {
                    width: calc(100% - 32px) !important;
                    max-width: none !important;
                    margin: 16px !important;
                }

                /* Modal padding */
                .delete-modal-content > div,
                div[style*="padding: 1rem 1.25rem"],
                div[style*="padding: 1.25rem"] {
                    padding: 12px 16px !important;
                }

                /* Modal buttons */
                .delete-modal-content button,
                div[style*="max-width: 450px"] button,
                div[style*="max-width: 400px"] button {
                    min-height: 40px !important;
                }
            }

            /* ============================================
               FORM RESPONSIVE STYLES
               ============================================ */
            @media (max-width: 768px) {
                /* Form inputs - larger touch targets */
                input[type="text"],
                input[type="email"],
                input[type="password"],
                input[type="number"],
                input[type="tel"],
                input[type="search"],
                select,
                textarea {
                    min-height: 40px !important;
                    font-size: 14px !important;
                }

                /* Form labels */
                label {
                    font-size: 12px !important;
                }

                /* Form grid - stack on mobile */
                .grid.grid-cols-2,
                .grid.grid-cols-3 {
                    grid-template-columns: 1fr !important;
                }
            }

            /* ============================================
               CARD / DETAIL VIEW RESPONSIVE STYLES
               ============================================ */
            @media (max-width: 768px) {
                /* Detail cards */
                .bg-white.border.border-gray-200 {
                    border-left: none !important;
                    border-right: none !important;
                    border-radius: 0 !important;
                }

                /* Card padding */
                .px-6 {
                    padding-left: 16px !important;
                    padding-right: 16px !important;
                }
                .py-4 {
                    padding-top: 12px !important;
                    padding-bottom: 12px !important;
                }
            }

            /* Touch-friendly improvements */
            @media (max-width: 1024px) {
                /* Larger touch targets for buttons */
                button, a.inline-flex, .dropdown-trigger {
                    min-height: 40px;
                }

                /* Better tap feedback */
                button:active, a:active {
                    opacity: 0.8;
                }

                /* Prevent text selection on interactive elements */
                button, a, .dropdown-trigger {
                    -webkit-user-select: none;
                    user-select: none;
                    -webkit-tap-highlight-color: transparent;
                }
            }

            /* ============================================
               EXTERNAL MODULE & HELPDESK RESPONSIVE STYLES
               Mobile & Tablet Responsive UI/UX
               ============================================ */

            /* ============================================
               TASK 1: BASE RESPONSIVE COMPONENTS
               Filter Section, Data Table, Page Header
               ============================================ */

            /* FILTER SECTION RESPONSIVE DESIGN */
            @media (max-width: 480px) {
                /* Filter container - vertical stack */
                .filter-section,
                form.flex.items-center.gap-2 {
                    display: flex !important;
                    flex-direction: column !important;
                    gap: 10px !important;
                    padding: 12px 16px !important;
                    background-color: #f9fafb !important;
                    border-radius: 8px !important;
                    margin-bottom: 16px !important;
                }
                
                /* Search input - full width */
                form.flex.items-center.gap-2 input[type="search"],
                form.flex.items-center.gap-2 input[type="text"]:first-of-type {
                    width: 100% !important;
                    flex: 1 1 100% !important;
                    min-height: 44px !important;
                    padding: 12px 16px !important;
                    border: 1px solid #e5e7eb !important;
                    border-radius: 8px !important;
                    font-size: 14px !important;
                }
                
                /* Dropdowns - 2 column grid */
                form.flex.items-center.gap-2 select {
                    flex: 1 1 calc(50% - 5px) !important;
                    min-width: calc(50% - 5px) !important;
                    min-height: 44px !important;
                    padding: 10px 12px !important;
                    border: 1px solid #e5e7eb !important;
                    border-radius: 8px !important;
                    font-size: 12px !important;
                }
                
                /* Action buttons - 2 column grid */
                form.flex.items-center.gap-2 button[type="submit"],
                form.flex.items-center.gap-2 button[type="button"],
                form.flex.items-center.gap-2 a.inline-flex {
                    flex: 1 1 calc(50% - 5px) !important;
                    min-height: 44px !important;
                    padding: 12px 16px !important;
                    border-radius: 8px !important;
                    font-size: 12px !important;
                    font-weight: 500 !important;
                    justify-content: center !important;
                }
            }

            @media (min-width: 481px) and (max-width: 767px) {
                form.flex.items-center.gap-2 {
                    flex-wrap: wrap !important;
                    gap: 8px !important;
                    padding: 12px 16px !important;
                }
                form.flex.items-center.gap-2 input[type="search"],
                form.flex.items-center.gap-2 input[type="text"]:first-of-type {
                    flex: 1 1 100% !important;
                    min-height: 44px !important;
                }
                form.flex.items-center.gap-2 select {
                    flex: 1 1 auto !important;
                    min-width: 100px !important;
                    min-height: 44px !important;
                }
                form.flex.items-center.gap-2 button {
                    min-height: 44px !important;
                }
            }

            @media (min-width: 768px) and (max-width: 1024px) {
                form.flex.items-center.gap-2 {
                    flex-wrap: wrap !important;
                    gap: 10px !important;
                    padding: 14px 20px !important;
                }
                form.flex.items-center.gap-2 input[type="search"],
                form.flex.items-center.gap-2 input[type="text"]:first-of-type {
                    flex: 1 1 40% !important;
                    min-width: 200px !important;
                    min-height: 44px !important;
                }
                form.flex.items-center.gap-2 select {
                    min-height: 44px !important;
                }
                form.flex.items-center.gap-2 button {
                    min-height: 44px !important;
                }
            }

            /* DATA TABLE RESPONSIVE DESIGN */
            @media (max-width: 480px) {
                /* Table container - horizontal scroll with shadow */
                .overflow-x-auto {
                    position: relative !important;
                    -webkit-overflow-scrolling: touch !important;
                    border-radius: 8px !important;
                }
                .overflow-x-auto::after {
                    content: '' !important;
                    position: absolute !important;
                    right: 0 !important;
                    top: 0 !important;
                    bottom: 0 !important;
                    width: 20px !important;
                    background: linear-gradient(to left, rgba(0,0,0,0.05), transparent) !important;
                    pointer-events: none !important;
                }
                
                /* Table base styles */
                table {
                    min-width: 500px !important;
                    border-collapse: separate !important;
                    border-spacing: 0 !important;
                }
                
                /* Table header */
                table thead {
                    background-color: #f8fafc !important;
                    position: sticky !important;
                    top: 0 !important;
                    z-index: 10 !important;
                }
                table thead th {
                    padding: 12px 14px !important;
                    font-size: 10px !important;
                    font-weight: 600 !important;
                    text-transform: uppercase !important;
                    letter-spacing: 0.5px !important;
                    color: #64748b !important;
                    white-space: nowrap !important;
                }
                
                /* Table body */
                table tbody tr {
                    transition: background-color 0.15s ease !important;
                }
                table tbody tr:hover {
                    background-color: #f8fafc !important;
                }
                table tbody td {
                    padding: 12px 14px !important;
                    font-size: 12px !important;
                    color: #334155 !important;
                    vertical-align: middle !important;
                }
                
                /* First column sticky on mobile */
                table tbody td:first-child,
                table thead th:first-child {
                    position: sticky !important;
                    left: 0 !important;
                    background-color: inherit !important;
                    z-index: 5 !important;
                    box-shadow: 2px 0 4px rgba(0,0,0,0.05) !important;
                }
                table thead th:first-child {
                    background-color: #f8fafc !important;
                    z-index: 11 !important;
                }
                table tbody tr:hover td:first-child {
                    background-color: #f8fafc !important;
                }
                
                /* Action buttons in table */
                .inline-flex.items-center.bg-white.border {
                    width: 32px !important;
                    height: 32px !important;
                    padding: 0 !important;
                    border-radius: 6px !important;
                }
                .inline-flex.items-center.bg-white.border .material-symbols-outlined {
                    font-size: 16px !important;
                }
            }

            @media (min-width: 481px) and (max-width: 767px) {
                table {
                    min-width: 600px !important;
                }
                table thead th {
                    padding: 14px 16px !important;
                    font-size: 11px !important;
                }
                table tbody td {
                    padding: 14px 16px !important;
                    font-size: 12px !important;
                }
            }

            @media (min-width: 768px) and (max-width: 1024px) {
                table thead th {
                    padding: 14px 18px !important;
                    font-size: 11px !important;
                }
                table tbody td {
                    padding: 14px 18px !important;
                    font-size: 12px !important;
                }
                .inline-flex.items-center.bg-white.border {
                    width: 36px !important;
                    height: 36px !important;
                }
            }

            /* PAGE HEADER RESPONSIVE DESIGN */
            @media (max-width: 480px) {
                /* Page header container */
                .bg-white.border.border-gray-200 > .px-6.py-4.flex.items-center.justify-between {
                    flex-direction: column !important;
                    gap: 12px !important;
                    padding: 14px 16px !important;
                    align-items: stretch !important;
                }
                
                /* Title section */
                .bg-white.border.border-gray-200 > .px-6.py-4.flex > div:first-child h2 {
                    font-size: 16px !important;
                    font-weight: 600 !important;
                }
                .bg-white.border.border-gray-200 > .px-6.py-4.flex > div:first-child p {
                    font-size: 11px !important;
                }
                
                /* Action buttons - full width */
                .bg-white.border.border-gray-200 > .px-6.py-4.flex > .flex.items-center.gap-2 {
                    width: 100% !important;
                    justify-content: flex-start !important;
                }
                .bg-white.border.border-gray-200 > .px-6.py-4.flex > .flex.items-center.gap-2 > a,
                .bg-white.border.border-gray-200 > .px-6.py-4.flex > .flex.items-center.gap-2 > button {
                    flex: 1 !important;
                    min-height: 44px !important;
                    justify-content: center !important;
                }
            }

            @media (min-width: 481px) and (max-width: 767px) {
                .bg-white.border.border-gray-200 > .px-6.py-4.flex {
                    padding: 14px 16px !important;
                }
                .bg-white.border.border-gray-200 > .px-6.py-4.flex > .flex.items-center.gap-2 > a,
                .bg-white.border.border-gray-200 > .px-6.py-4.flex > .flex.items-center.gap-2 > button {
                    min-height: 40px !important;
                }
            }

            @media (min-width: 768px) and (max-width: 1024px) {
                .bg-white.border.border-gray-200 > .px-6.py-4.flex {
                    padding: 16px 20px !important;
                }
            }

            /* ============================================
               TASK 2: EXTERNAL PROJECTS PAGE
               ============================================ */
            /* Projects page specific styles already covered by base components */

            /* ============================================
               TASK 3: EXTERNAL REPORTS PAGE
               ============================================ */
            @media (max-width: 480px) {
                /* Stat cards - 2 columns */
                div[style*="grid-template-columns: repeat(6, 1fr)"],
                div[style*="grid-template-columns: repeat(4, 1fr)"],
                div[style*="grid-template-columns: repeat(3, 1fr)"] {
                    grid-template-columns: repeat(2, 1fr) !important;
                    gap: 12px !important;
                }
                
                /* Chart sections - single column */
                div[style*="grid-template-columns: 2fr 1fr"],
                div[style*="grid-template-columns: 1fr 1fr"] {
                    grid-template-columns: 1fr !important;
                    gap: 16px !important;
                }
            }

            @media (min-width: 481px) and (max-width: 767px) {
                div[style*="grid-template-columns: repeat(6, 1fr)"],
                div[style*="grid-template-columns: repeat(4, 1fr)"] {
                    grid-template-columns: repeat(3, 1fr) !important;
                }
            }

            @media (min-width: 768px) and (max-width: 1024px) {
                div[style*="grid-template-columns: repeat(6, 1fr)"] {
                    grid-template-columns: repeat(3, 1fr) !important;
                }
                div[style*="grid-template-columns: 2fr 1fr"] {
                    grid-template-columns: repeat(2, 1fr) !important;
                }
            }

            /* ============================================
               TASK 4: EXTERNAL ATTACHMENTS PAGE
               ============================================ */
            /* Attachments page specific styles already covered by base components */

            /* ============================================
               TASK 5: EXTERNAL SETTINGS PAGE
               Tab Navigation & Content Areas
               ============================================ */
            @media (max-width: 480px) {
                /* Tab navigation - horizontal scroll */
                nav.flex[aria-label="Tabs"],
                .flex.border-b.border-gray-200 {
                    overflow-x: auto !important;
                    -webkit-overflow-scrolling: touch !important;
                    scrollbar-width: none !important;
                    -ms-overflow-style: none !important;
                }
                nav.flex[aria-label="Tabs"]::-webkit-scrollbar,
                .flex.border-b.border-gray-200::-webkit-scrollbar {
                    display: none !important;
                }
                nav.flex[aria-label="Tabs"] a,
                .flex.border-b.border-gray-200 a {
                    flex-shrink: 0 !important;
                    white-space: nowrap !important;
                    min-height: 44px !important;
                    padding: 12px 16px !important;
                }
            }

            @media (min-width: 768px) and (max-width: 1024px) {
                nav.flex[aria-label="Tabs"] a,
                .flex.border-b.border-gray-200 a {
                    padding: 10px 12px !important;
                    font-size: 11px !important;
                }
            }

            /* ============================================
               TASK 6: MODAL FORMS RESPONSIVE
               ============================================ */
            @media (max-width: 480px) {
                /* Modal - full width */
                div[role="dialog"],
                .fixed.inset-0.z-50 > div {
                    width: calc(100% - 24px) !important;
                    max-width: none !important;
                    margin: 12px !important;
                    max-height: calc(100vh - 24px) !important;
                }
                
                /* Form fields - single column */
                div[role="dialog"] .grid.grid-cols-2,
                div[role="dialog"] .grid.grid-cols-3,
                .fixed.inset-0.z-50 .grid.grid-cols-2,
                .fixed.inset-0.z-50 .grid.grid-cols-3 {
                    grid-template-columns: 1fr !important;
                }
                
                /* Form inputs - touch friendly */
                div[role="dialog"] input,
                div[role="dialog"] select,
                div[role="dialog"] textarea,
                .fixed.inset-0.z-50 input,
                .fixed.inset-0.z-50 select,
                .fixed.inset-0.z-50 textarea {
                    min-height: 44px !important;
                    font-size: 16px !important; /* Prevents iOS zoom */
                }
                
                /* Modal content - scrollable */
                div[role="dialog"] > div > div:nth-child(2),
                .fixed.inset-0.z-50 > div > div:nth-child(2) {
                    max-height: 60vh !important;
                    overflow-y: auto !important;
                }
            }

            @media (min-width: 481px) and (max-width: 767px) {
                div[role="dialog"],
                .fixed.inset-0.z-50 > div {
                    width: calc(100% - 32px) !important;
                    max-width: 500px !important;
                }
            }

            @media (min-width: 768px) and (max-width: 1024px) {
                div[role="dialog"],
                .fixed.inset-0.z-50 > div {
                    width: 90% !important;
                    max-width: 520px !important;
                }
            }

            /* ============================================
               TASK 8: HELPDESK INDEX PAGE
               ============================================ */
            @media (max-width: 480px) {
                /* Helpdesk page header - stack */
                body[class*="helpdesk"] .bg-white.border.border-gray-200 > .px-6.py-4.flex,
                [x-data*="helpdesk"] .bg-white.border.border-gray-200 > .px-6.py-4.flex {
                    flex-direction: column !important;
                    gap: 12px !important;
                    align-items: stretch !important;
                }
                body[class*="helpdesk"] .bg-white.border.border-gray-200 > .px-6.py-4.flex button,
                [x-data*="helpdesk"] .bg-white.border.border-gray-200 > .px-6.py-4.flex button {
                    width: 100% !important;
                    justify-content: center !important;
                }
            }

            /* ============================================
               TASK 9: HELPDESK TICKET DETAIL PAGE
               ============================================ */
            @media (max-width: 480px) {
                /* Info cards - single column */
                body[class*="helpdesk"] div[style*="grid-template-columns: 1fr 1fr"],
                [x-data*="ticket"] div[style*="grid-template-columns: 1fr 1fr"] {
                    grid-template-columns: 1fr !important;
                    gap: 16px !important;
                }
                
                /* Info card internal grid - 2 columns */
                body[class*="helpdesk"] div[style*="grid-template-columns: 1fr 1fr"] > div > div[style*="grid-template-columns"],
                [x-data*="ticket"] div[style*="grid-template-columns: 1fr 1fr"] > div > div[style*="grid-template-columns"] {
                    grid-template-columns: 1fr 1fr !important;
                }
                
                /* Main content - stack (chat first, then sidebar) */
                body[class*="helpdesk"] div[style*="grid-template-columns: 2fr 1fr"],
                [x-data*="ticket"] div[style*="grid-template-columns: 2fr 1fr"] {
                    grid-template-columns: 1fr !important;
                }
                
                /* Chat container - full width, reduced height */
                body[class*="helpdesk"] div[style*="height: 600px"],
                [x-data*="ticket"] div[style*="height: 600px"] {
                    height: auto !important;
                    max-height: 400px !important;
                }
                
                /* Reply form - full width */
                body[class*="helpdesk"] textarea,
                [x-data*="ticket"] textarea {
                    min-height: 80px !important;
                }
            }

            @media (min-width: 481px) and (max-width: 767px) {
                body[class*="helpdesk"] div[style*="height: 600px"],
                [x-data*="ticket"] div[style*="height: 600px"] {
                    height: 350px !important;
                }
            }

            @media (min-width: 768px) and (max-width: 1024px) {
                body[class*="helpdesk"] div[style*="grid-template-columns: 2fr 1fr"],
                [x-data*="ticket"] div[style*="grid-template-columns: 2fr 1fr"] {
                    grid-template-columns: 1fr !important;
                }
                body[class*="helpdesk"] div[style*="height: 600px"],
                [x-data*="ticket"] div[style*="height: 600px"] {
                    height: 450px !important;
                }
            }

            @media (min-width: 1024px) and (max-width: 1280px) {
                body[class*="helpdesk"] div[style*="height: 600px"],
                [x-data*="ticket"] div[style*="height: 600px"] {
                    height: 500px !important;
                }
            }

            /* ============================================
               TASK 10: HELPDESK CREATE TICKET MODAL
               ============================================ */
            @media (max-width: 480px) {
                /* Create ticket modal - full width */
                #create-modal > div,
                div[x-show*="createModal"] > div {
                    width: calc(100% - 24px) !important;
                    max-width: none !important;
                    margin: 12px !important;
                }
                
                /* Custom dropdowns - touch friendly */
                #priority-dropdown-list > div,
                #category-dropdown-list > div {
                    min-height: 44px !important;
                    padding: 12px !important;
                }
                
                /* File input - larger touch area */
                #create-modal input[type="file"],
                div[x-show*="createModal"] input[type="file"] {
                    min-height: 44px !important;
                }
            }

            @media (min-width: 481px) and (max-width: 1024px) {
                #create-modal > div,
                div[x-show*="createModal"] > div {
                    width: calc(100% - 32px) !important;
                    max-width: 500px !important;
                }
            }

            /* ============================================
               TASK 11: TOUCH ACCESSIBILITY
               Minimum 44px touch targets
               ============================================ */
            @media (max-width: 1024px) {
                /* All buttons */
                button,
                a.inline-flex,
                input[type="submit"],
                input[type="button"] {
                    min-height: 44px !important;
                }
                
                /* Form inputs */
                input[type="text"],
                input[type="email"],
                input[type="password"],
                input[type="number"],
                input[type="tel"],
                input[type="search"],
                input[type="date"],
                select,
                textarea {
                    min-height: 44px !important;
                }
                
                /* Pagination controls */
                nav[aria-label="Pagination"] a,
                nav[aria-label="Pagination"] button {
                    min-height: 44px !important;
                    min-width: 44px !important;
                }
                
                /* Tab navigation items */
                nav[aria-label="Tabs"] a,
                .flex.border-b.border-gray-200 a {
                    min-height: 44px !important;
                }
                
                /* Checkbox and toggle inputs */
                input[type="checkbox"],
                input[type="radio"] {
                    min-width: 20px !important;
                    min-height: 20px !important;
                }
            }

            /* ============================================
               SAFE AREA INSETS FOR NOTCHED DEVICES
               ============================================ */
            @supports (padding: max(0px)) {
                @media (max-width: 1024px) {
                    div[role="dialog"],
                    .fixed.inset-0.z-50 > div {
                        padding-bottom: max(16px, env(safe-area-inset-bottom)) !important;
                    }
                }
            }
        </style>
    </head>
    <body class="font-poppins antialiased bg-gray-50"
          x-data="{ sidebarCollapsed: false }"
          x-init="
              Alpine.store('mobileMenu', { open: false });
              $watch('$store.mobileMenu.open', value => {
                  if (value) {
                      document.body.style.overflow = 'hidden';
                  } else {
                      document.body.style.overflow = '';
                  }
              });
          "
          @sidebar-toggled.window="sidebarCollapsed = $event.detail.collapsed"
          @toggle-mobile-menu.window="$store.mobileMenu.open = !$store.mobileMenu.open"
          @close-mobile-menu.window="$store.mobileMenu.open = false">
        <div class="min-h-screen flex flex-col">
            <!-- Sidebar -->
            <x-sidebar />
            
            <!-- Mobile/Tablet sidebar overlay -->
            <div x-show="$store.mobileMenu?.open" 
                 @click="$dispatch('close-mobile-menu')"
                 class="sidebar-overlay"
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 style="display: none;">
            </div>

            <!-- Main content -->
            <div class="main-content flex flex-col flex-1"
                 :class="sidebarCollapsed ? 'main-content-collapsed' : 'main-content-expanded'">
                <!-- Header with Page Title -->
                <x-header :title="View::getSection('page-title', 'Dashboard')" />

                <!-- Page Content -->
                <main class="p-6 flex-1">
                    @yield('content')
                </main>

                <!-- Footer -->
                <x-footer />
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <x-modals.delete-confirmation />

        <!-- Global Scripts -->
        <script>
            // Dropdown Action Buttons
            document.addEventListener('DOMContentLoaded', function() {
                // Close all dropdowns when clicking outside
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.dropdown-container')) {
                        document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
                            menu.classList.add('hidden');
                        });
                    }
                });

                // Toggle dropdown on trigger click
                document.querySelectorAll('.dropdown-trigger').forEach(function(trigger) {
                    trigger.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const container = this.closest('.dropdown-container');
                        const menu = container.querySelector('.dropdown-menu');
                        
                        // Close other dropdowns
                        document.querySelectorAll('.dropdown-menu').forEach(function(m) {
                            if (m !== menu) m.classList.add('hidden');
                        });
                        
                        // Position and toggle this dropdown
                        if (menu.classList.contains('hidden')) {
                            // Get the pill container (parent of dropdown-container)
                            const pillContainer = container.closest('.inline-flex');
                            const pillRect = pillContainer ? pillContainer.getBoundingClientRect() : this.getBoundingClientRect();
                            
                            // Position below the pill, aligned to the left
                            menu.style.top = (pillRect.bottom + 4) + 'px';
                            menu.style.left = pillRect.left + 'px';
                            menu.classList.remove('hidden');
                        } else {
                            menu.classList.add('hidden');
                        }
                    });
                });
            });

            // Delete Modal Functions
            let deleteCallback = null;
            let deleteModalCodes = {}; // Store verification codes per modal

            function generateVerificationCode() {
                const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Exclude confusing chars: I, O, 0, 1
                let code = '';
                for (let i = 0; i < 6; i++) {
                    code += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                return code;
            }

            function openDeleteModal(modalId, callback) {
                const modal = document.getElementById(modalId || 'delete-modal');
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.style.display = 'flex';
                    deleteCallback = callback;
                    
                    // Generate new verification code
                    const code = generateVerificationCode();
                    deleteModalCodes[modalId || 'delete-modal'] = code;
                    
                    // Display the code
                    const codeDisplay = document.getElementById((modalId || 'delete-modal') + '-verification-code');
                    if (codeDisplay) {
                        codeDisplay.textContent = code;
                    }
                    
                    // Reset input and button
                    const codeInput = document.getElementById((modalId || 'delete-modal') + '-code-input');
                    const confirmBtn = document.getElementById((modalId || 'delete-modal') + '-confirm-btn');
                    const errorMsg = document.getElementById((modalId || 'delete-modal') + '-error-msg');
                    
                    if (codeInput) {
                        codeInput.value = '';
                        codeInput.style.borderColor = '#d1d5db';
                    }
                    if (confirmBtn) {
                        confirmBtn.disabled = true;
                        confirmBtn.style.opacity = '0.5';
                        confirmBtn.style.cursor = 'not-allowed';
                    }
                    if (errorMsg) {
                        errorMsg.style.display = 'none';
                    }
                }
            }

            function validateDeleteCode(modalId) {
                const id = modalId || 'delete-modal';
                const codeInput = document.getElementById(id + '-code-input');
                const confirmBtn = document.getElementById(id + '-confirm-btn');
                const errorMsg = document.getElementById(id + '-error-msg');
                const expectedCode = deleteModalCodes[id];
                
                if (!codeInput || !confirmBtn) return;
                
                const enteredCode = codeInput.value.trim();
                
                if (enteredCode.length === 6) {
                    if (enteredCode === expectedCode) {
                        // Code matches - enable button
                        confirmBtn.disabled = false;
                        confirmBtn.style.opacity = '1';
                        confirmBtn.style.cursor = 'pointer';
                        codeInput.style.borderColor = '#22c55e';
                        if (errorMsg) errorMsg.style.display = 'none';
                    } else {
                        // Code doesn't match
                        confirmBtn.disabled = true;
                        confirmBtn.style.opacity = '0.5';
                        confirmBtn.style.cursor = 'not-allowed';
                        codeInput.style.borderColor = '#dc2626';
                        if (errorMsg) errorMsg.style.display = 'block';
                    }
                } else {
                    // Not enough digits
                    confirmBtn.disabled = true;
                    confirmBtn.style.opacity = '0.5';
                    confirmBtn.style.cursor = 'not-allowed';
                    codeInput.style.borderColor = '#d1d5db';
                    if (errorMsg) errorMsg.style.display = 'none';
                }
            }

            function closeDeleteModal(modalId) {
                const modal = document.getElementById(modalId || 'delete-modal');
                if (modal) {
                    modal.classList.add('hidden');
                    modal.style.display = 'none';
                    deleteCallback = null;
                    delete deleteModalCodes[modalId || 'delete-modal'];
                }
            }

            function confirmDelete(modalId) {
                const id = modalId || 'delete-modal';
                const confirmBtn = document.getElementById(id + '-confirm-btn');
                
                // Only proceed if button is enabled (code verified)
                if (confirmBtn && confirmBtn.disabled) {
                    return;
                }
                
                if (deleteCallback && typeof deleteCallback === 'function') {
                    deleteCallback();
                }
                closeDeleteModal(modalId);
            }

            // Attach confirm button handler
            document.addEventListener('DOMContentLoaded', function() {
                const confirmBtn = document.getElementById('delete-modal-confirm-btn');
                if (confirmBtn) {
                    confirmBtn.addEventListener('click', function() {
                        confirmDelete('delete-modal');
                    });
                }
            });

            // Simple delete modal with URL (POS pattern)
            window.showDeleteModal = function(deleteUrl) {
                const modal = document.getElementById('delete-modal');
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.style.display = 'flex';
                    
                    // Generate new verification code
                    const code = generateVerificationCode();
                    deleteModalCodes['delete-modal'] = code;
                    
                    // Display the code
                    const codeDisplay = document.getElementById('delete-modal-verification-code');
                    if (codeDisplay) {
                        codeDisplay.textContent = code;
                    }
                    
                    // Reset input and button
                    const codeInput = document.getElementById('delete-modal-code-input');
                    const confirmBtn = document.getElementById('delete-modal-confirm-btn');
                    const errorMsg = document.getElementById('delete-modal-error-msg');
                    
                    if (codeInput) {
                        codeInput.value = '';
                        codeInput.style.borderColor = '#d1d5db';
                    }
                    if (confirmBtn) {
                        confirmBtn.disabled = true;
                        confirmBtn.style.opacity = '0.5';
                        confirmBtn.style.cursor = 'not-allowed';
                    }
                    if (errorMsg) {
                        errorMsg.style.display = 'none';
                    }
                    
                    // Set up the confirm button to submit a DELETE request
                    if (confirmBtn) {
                        confirmBtn.onclick = function() {
                            // Only proceed if code is verified
                            if (confirmBtn.disabled) return;
                            
                            // Create and submit a form
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = deleteUrl;
                            form.innerHTML = `
                                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                                <input type="hidden" name="_method" value="DELETE">
                            `;
                            document.body.appendChild(form);
                            form.submit();
                        };
                    }
                }
            };
        </script>

        <!-- Print Detection Script -->
        <script>
            (function() {
                let printLogged = false;
                
                // Detect print via beforeprint event
                window.addEventListener('beforeprint', function() {
                    if (!printLogged) {
                        printLogged = true;
                        logPrintAction();
                        // Reset after 5 seconds to allow logging again
                        setTimeout(function() { printLogged = false; }, 5000);
                    }
                });

                // Detect Ctrl+P / Cmd+P
                document.addEventListener('keydown', function(e) {
                    if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                        if (!printLogged) {
                            printLogged = true;
                            logPrintAction();
                            setTimeout(function() { printLogged = false; }, 5000);
                        }
                    }
                });

                function logPrintAction() {
                    // Determine module from current URL
                    let module = 'general';
                    const path = window.location.pathname;
                    
                    if (path.includes('/helpdesk')) module = 'helpdesk';
                    else if (path.includes('/internal/inventory')) module = 'internal_inventory';
                    else if (path.includes('/internal/employee')) module = 'internal_employee';
                    else if (path.includes('/internal/credentials')) module = 'internal_credentials';
                    else if (path.includes('/internal/download')) module = 'internal_downloads';
                    else if (path.includes('/external/inventory')) module = 'external_inventory';
                    else if (path.includes('/external/projects')) module = 'external_projects';
                    else if (path.includes('/external/reports')) module = 'external_reports';
                    else if (path.includes('/settings/users')) module = 'settings_users';
                    else if (path.includes('/settings/roles')) module = 'settings_roles';
                    else if (path.includes('/settings/activity-logs')) module = 'settings_activity_logs';
                    else if (path.includes('/settings')) module = 'settings';
                    else if (path.includes('/dashboard')) module = 'dashboard';

                    fetch('{{ route("settings.activity-logs.print") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            module: module,
                            description: 'Printed page: ' + document.title,
                            page_url: window.location.href,
                            page_title: document.title
                        })
                    }).catch(function(err) {
                        console.log('Print log failed:', err);
                    });
                }
            })();
        </script>

        <!-- Page-specific Scripts -->
        @stack('scripts')
    </body>
</html>
