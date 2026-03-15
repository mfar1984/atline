@php
    $menuItems = [
        [
            'label' => 'Overview',
            'icon' => 'dashboard',
            'route' => 'dashboard',
            'active' => 'dashboard*',
            'module' => 'overview',
        ],
        [
            'label' => 'Internal',
            'icon' => 'home_storage',
            'children' => [
                ['label' => 'Credentials', 'route' => 'internal.credentials.index', 'active' => 'internal.credentials*', 'module' => 'internal_credentials'],
                ['label' => 'Download', 'route' => 'internal.download.index', 'active' => 'internal.download*', 'module' => 'internal_download'],
                ['label' => 'Employee', 'route' => 'internal.employee.index', 'active' => 'internal.employee*', 'module' => 'internal_employee'],
                ['label' => 'Inventory', 'route' => 'internal.inventory.index', 'active' => 'internal.inventory*', 'module' => 'internal_inventory']
            ]
        ],
        [
            'label' => 'External',
            'icon' => 'public',
            'children' => [
                ['label' => 'Projects', 'route' => 'external.projects.index', 'active' => 'external.projects*', 'module' => 'external_projects'],
                ['label' => 'Inventory', 'route' => 'external.inventory.index', 'active' => 'external.inventory*', 'module' => 'external_inventory'],
                ['label' => 'Reports', 'route' => 'external.reports.index', 'active' => 'external.reports*', 'module' => 'external_reports'],
                ['label' => 'Attachments', 'route' => 'external.attachments.index', 'active' => 'external.attachments*', 'module' => 'external_attachments'],
                ['label' => 'Settings', 'route' => 'external.settings.index', 'active' => 'external.settings*', 'module' => 'external_settings']
            ]
        ],
        [
            'type' => 'divider'
        ],
        [
            'label' => 'Helpdesk',
            'icon' => 'support_agent',
            'route' => 'helpdesk.index',
            'active' => 'helpdesk*',
            'module' => 'helpdesk',
        ],
        [
            'type' => 'divider'
        ],
        [
            'label' => 'System Settings',
            'icon' => 'settings',
            'children' => [
                ['label' => 'System Configuration', 'route' => 'settings.configuration.index', 'active' => 'settings.configuration*', 'module' => 'settings_configuration'],
                ['label' => 'Group Roles', 'route' => 'settings.roles.index', 'active' => 'settings.roles*', 'module' => 'settings_roles'],
                ['label' => 'User Management', 'route' => 'settings.users.index', 'active' => 'settings.users*', 'module' => 'settings_users'],
                ['label' => 'Integrations', 'route' => 'settings.integrations.index', 'active' => 'settings.integrations*', 'module' => 'settings_integrations'],
                ['label' => 'Activity Logs', 'route' => 'settings.activity-logs.index', 'active' => 'settings.activity-logs*', 'module' => 'settings_activity_logs']
            ]
        ]
    ];
@endphp

<div class="sidebar sidebar-expanded" 
     x-data="{ collapsed: false, openMenus: {} }"
     :class="{ 
         'sidebar-collapsed': collapsed, 
         'sidebar-expanded': !collapsed, 
         'sidebar-mobile-open': $store.mobileMenu?.open 
     }"
     @toggle-sidebar.window="collapsed = !collapsed"
     @toggle-mobile-menu.window="$el.classList.toggle('sidebar-mobile-open')"
     @close-mobile-menu.window="$el.classList.remove('sidebar-mobile-open')">

    <!-- Close button for mobile/tablet -->
    <button type="button"
            @click="$dispatch('close-mobile-menu')"
            class="sidebar-close-btn absolute top-4 right-4 p-2 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none lg:hidden"
            style="z-index: 10; -webkit-tap-highlight-color: transparent;">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>

    <!-- Sidebar Header -->
    <div class="sidebar-header" style="position: relative;">
        <div class="flex items-center justify-center relative">
            <div x-show="!collapsed" class="flex items-center justify-center">
                <div class="h-10 rounded-lg flex items-center justify-center overflow-hidden">
                    <img src="{{ \App\Models\SystemSetting::logoPath() }}" alt="Logo" class="h-full object-contain" onerror="this.style.display='none'; this.parentElement.classList.add('bg-blue-600', 'w-10'); this.parentElement.innerHTML='<span class=\'material-symbols-outlined text-white\' style=\'font-size: 24px;\'>deployed_code</span>';">
                </div>
            </div>
            <div x-show="collapsed" class="w-10 h-10 rounded-lg flex items-center justify-center overflow-hidden">
                <img src="{{ \App\Models\SystemSetting::iconPath() }}" alt="Icon" class="w-full h-full object-contain" onerror="this.style.display='none'; this.parentElement.classList.add('bg-blue-600'); this.parentElement.innerHTML='<span class=\'material-symbols-outlined text-white\' style=\'font-size: 24px;\'>deployed_code</span>';">
            </div>
            <button @click="collapsed = !collapsed; $dispatch('sidebar-toggled', { collapsed: collapsed })"
                    class="sidebar-toggle absolute right-0 top-1/2 -translate-y-1/2" x-show="!collapsed" type="button">
                <span class="material-icons-outlined" style="font-size: 20px;">widgets</span>
            </button>
            <button @click="collapsed = !collapsed; $dispatch('sidebar-toggled', { collapsed: collapsed })"
                    class="sidebar-toggle" x-show="collapsed" type="button">
                <span class="material-symbols-outlined" style="font-size: 20px;">arrow_circle_right</span>
            </button>
        </div>
    </div>

    <!-- Sidebar Navigation -->
    <nav class="sidebar-nav mt-4 overflow-y-auto" style="max-height: calc(100vh - 100px);">
        <div class="space-y-1">
            @foreach($menuItems as $item)
                @if(isset($item['type']) && $item['type'] === 'divider')
                    <div class="sidebar-separator"><hr class="sidebar-separator-line"></div>
                @elseif(isset($item['children']))
                    @php
                        $isAnyChildActive = false;
                        $visibleChildren = [];
                        foreach ($item['children'] as $child) {
                            // Check if user has access to this module
                            if (auth()->user()->hasModuleAccess($child['module'] ?? '')) {
                                $visibleChildren[] = $child;
                                if (request()->routeIs($child['active'] ?? '')) {
                                    $isAnyChildActive = true;
                                }
                            }
                        }
                        $shouldExpand = $isAnyChildActive;
                    @endphp
                    @if(count($visibleChildren) > 0)
                    <div x-data="{ open: {{ $shouldExpand ? 'true' : 'false' }}, hovered: false }"
                         class="relative" @mouseenter="hovered = collapsed" @mouseleave="hovered = false">
                        <button @click="if(!collapsed) open = !open"
                                class="sidebar-nav-item {{ $shouldExpand ? 'sidebar-nav-item-parent-active' : 'sidebar-nav-item-inactive' }} w-full text-left">
                            <span class="material-symbols-outlined sidebar-nav-icon">{{ $item['icon'] }}</span>
                            <span x-show="!collapsed" class="flex-1">{{ $item['label'] }}</span>
                            <span x-show="!collapsed && !open" class="material-symbols-outlined ml-auto" style="font-size: 16px;">chevron_right</span>
                            <span x-show="!collapsed && open" class="material-symbols-outlined ml-auto" style="font-size: 16px;">expand_more</span>
                        </button>
                        <div x-show="(collapsed && hovered) || (!collapsed && open)" x-transition
                             :class="collapsed ? 'submenu-dropdown' : 'submenu-container space-y-1'">
                            @foreach($visibleChildren as $child)
                                @php 
                                    $childUrl = '#';
                                    try { $childUrl = route($child['route']); } catch (\Exception $e) {}
                                @endphp
                                <a href="{{ $childUrl }}"
                                   class="submenu-item sidebar-nav-item {{ request()->routeIs($child['active'] ?? '') ? 'sidebar-nav-item-active' : 'sidebar-nav-item-inactive' }} text-sm">
                                    {{ $child['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @else
                    @if(auth()->user()->hasModuleAccess($item['module'] ?? ''))
                    @php 
                        $url = '#'; 
                        try { $url = route($item['route']); } catch (\Exception $e) {}
                    @endphp
                    <a href="{{ $url }}"
                       class="sidebar-nav-item {{ request()->routeIs($item['active'] ?? '') ? 'sidebar-nav-item-active' : 'sidebar-nav-item-inactive' }}">
                        <span class="material-symbols-outlined sidebar-nav-icon">{{ $item['icon'] }}</span>
                        <span x-show="!collapsed">{{ $item['label'] }}</span>
                    </a>
                    @endif
                @endif
            @endforeach
        </div>
    </nav>
</div>
