@props(['title' => ''])

<header class="header">
    <!-- Top Bar -->
    <div class="topbar">
        <div class="topbar-container">
            <!-- Left side - Welcome message and date -->
            <div class="topbar-left">
                <span class="welcome-text">Welcome, {{ Auth::user()->name ?? 'Administrator' }}</span>
                <span class="topbar-separator">|</span>
                <span class="current-date" x-data="{
                    time: '{{ now()->format('l d F Y H:i:s') }}',
                    updateTime() {
                        const now = new Date();
                        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                        const dayName = days[now.getDay()];
                        const day = now.getDate();
                        const month = months[now.getMonth()];
                        const year = now.getFullYear();
                        const hours = now.getHours().toString().padStart(2, '0');
                        const minutes = now.getMinutes().toString().padStart(2, '0');
                        const seconds = now.getSeconds().toString().padStart(2, '0');
                        this.time = `${dayName} ${day} ${month} ${year} ${hours}:${minutes}:${seconds}`;
                    }
                }"
                x-init="updateTime(); setInterval(() => updateTime(), 1000)"
                x-text="time">
                </span>
            </div>

            <!-- Right side - User menu -->
            <div class="topbar-right">
                <!-- User dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="topbar-user-btn">
                        <div class="user-avatar">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <span class="user-email">{{ Auth::user()->email ?? 'admin@atline.com' }}</span>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Dropdown menu -->
                    <div x-show="open" @click.away="open = false" x-transition class="topbar-dropdown">
                        <a href="{{ route('profile.index') }}" class="topbar-dropdown-item">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Profile
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="topbar-dropdown-item w-full text-left">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Breadcrumb Bar -->
    <div class="breadcrumb-bar">
        <div class="breadcrumb-container">
            <!-- Mobile/Tablet Menu Button - Hidden on desktop (lg and above) -->
            <button type="button" 
                    @click="$dispatch('toggle-mobile-menu')"
                    x-data="{ sidebarOpen: false }"
                    @toggle-mobile-menu.window="sidebarOpen = !sidebarOpen"
                    @close-mobile-menu.window="sidebarOpen = false"
                    class="mobile-menu-btn hidden items-center justify-center p-2 rounded-md text-gray-700 hover:text-blue-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 active:bg-blue-50 transition-colors"
                    style="min-width: 44px; min-height: 44px; -webkit-tap-highlight-color: transparent;">
                <svg x-show="!sidebarOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
                <svg x-show="sidebarOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Breadcrumb -->
            <x-breadcrumb />
        </div>
    </div>
</header>

<style>
    /* Mobile menu button visibility - show on tablet and mobile */
    @media (max-width: 1024px) {
        .mobile-menu-btn {
            display: flex !important;
        }
    }
    @media (min-width: 1025px) {
        .mobile-menu-btn {
            display: none !important;
        }
    }
</style>
