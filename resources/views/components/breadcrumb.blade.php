@php
    $currentRoute = request()->route() ? request()->route()->getName() : null;
    $breadcrumbs = [];
    
    // Home breadcrumb
    $breadcrumbs[] = [
        'type' => 'home',
        'url' => route('dashboard'),
        'name' => null,
    ];
    
    // Generate breadcrumb from route name
    if ($currentRoute) {
        $parts = explode('.', $currentRoute);
        $accumulated = [];
        
        foreach ($parts as $index => $part) {
            $accumulated[] = $part;
            $routeName = implode('.', $accumulated);
            $isLast = $index === count($parts) - 1;
            
            // Skip if it's just 'index' at the end
            if ($part === 'index') {
                continue;
            }
            
            $breadcrumbs[] = [
                'type' => $isLast ? 'current' : 'text',
                'url' => null,
                'name' => ucfirst(str_replace('-', ' ', $part)),
            ];
        }
    }
@endphp

<nav class="breadcrumb-nav flex items-center space-x-2 text-xs overflow-x-auto">
    @foreach($breadcrumbs as $index => $breadcrumb)
        @if($breadcrumb['type'] === 'home')
            <!-- Home icon -->
            <a href="{{ $breadcrumb['url'] }}" class="text-gray-500 hover:text-blue-600 transition-colors flex-shrink-0">
                <span class="material-symbols-outlined" style="font-size: 16px;">home</span>
            </a>
        @elseif($breadcrumb['type'] === 'link')
            <!-- Separator -->
            <span class="text-gray-400 flex-shrink-0">></span>
            <!-- Link -->
            <a href="{{ $breadcrumb['url'] }}" class="text-gray-600 hover:text-blue-600 transition-colors whitespace-nowrap">{{ $breadcrumb['name'] }}</a>
        @elseif($breadcrumb['type'] === 'text')
            <!-- Separator -->
            <span class="text-gray-400 flex-shrink-0">></span>
            <!-- Text only (no link) - hide on very small screens if not last -->
            <span class="text-gray-600 whitespace-nowrap breadcrumb-text">{{ $breadcrumb['name'] }}</span>
        @elseif($breadcrumb['type'] === 'current')
            <!-- Separator -->
            <span class="text-gray-400 flex-shrink-0">></span>
            <!-- Current page -->
            <span class="text-blue-600 font-semibold whitespace-nowrap">{{ $breadcrumb['name'] }}</span>
        @endif
    @endforeach
</nav>

<style>
    /* Breadcrumb responsive styles */
    .breadcrumb-nav {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .breadcrumb-nav::-webkit-scrollbar {
        display: none;
    }
    
    @media (max-width: 480px) {
        .breadcrumb-nav {
            font-size: 10px !important;
            max-width: calc(100vw - 80px);
        }
        .breadcrumb-nav .material-symbols-outlined {
            font-size: 14px !important;
        }
        /* Hide middle breadcrumb items on very small screens */
        .breadcrumb-nav .breadcrumb-text:not(:last-of-type) {
            display: none;
        }
        .breadcrumb-nav .breadcrumb-text:not(:last-of-type) + span.text-gray-400 {
            display: none;
        }
    }
    
    @media (min-width: 481px) and (max-width: 768px) {
        .breadcrumb-nav {
            font-size: 11px !important;
            max-width: calc(100vw - 100px);
        }
    }
</style>
