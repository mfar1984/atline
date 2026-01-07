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

<nav class="flex items-center space-x-2 text-xs">
    @foreach($breadcrumbs as $index => $breadcrumb)
        @if($breadcrumb['type'] === 'home')
            <!-- Home icon -->
            <a href="{{ $breadcrumb['url'] }}" class="text-gray-500 hover:text-blue-600 transition-colors">
                <span class="material-symbols-outlined" style="font-size: 16px;">home</span>
            </a>
        @elseif($breadcrumb['type'] === 'link')
            <!-- Separator -->
            <span class="text-gray-400">></span>
            <!-- Link -->
            <a href="{{ $breadcrumb['url'] }}" class="text-gray-600 hover:text-blue-600 transition-colors">{{ $breadcrumb['name'] }}</a>
        @elseif($breadcrumb['type'] === 'text')
            <!-- Separator -->
            <span class="text-gray-400">></span>
            <!-- Text only (no link) -->
            <span class="text-gray-600">{{ $breadcrumb['name'] }}</span>
        @elseif($breadcrumb['type'] === 'current')
            <!-- Separator -->
            <span class="text-gray-400">></span>
            <!-- Current page -->
            <span class="text-blue-600 font-semibold">{{ $breadcrumb['name'] }}</span>
        @endif
    @endforeach
</nav>
