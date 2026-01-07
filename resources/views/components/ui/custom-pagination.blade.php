@props([
    'paginator',
    'recordLabel' => 'records',
    'tabParam' => null,
])

@php
    use Illuminate\Pagination\LengthAwarePaginator;
    use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
    
    $isLengthAware = $paginator instanceof LengthAwarePaginator;
    $isPaginator = $paginator instanceof PaginatorContract;
    $hasPages = $isPaginator ? $paginator->hasPages() : false;
    $total = $isLengthAware ? $paginator->total() : ($isPaginator ? $paginator->count() : 0);
    $firstItem = $isPaginator ? ($paginator->firstItem() ?? 0) : 0;
    $lastItem = $isPaginator ? ($paginator->lastItem() ?? 0) : 0;
    
    // Build URL with tab parameter if provided
    $buildUrl = function($page) use ($paginator, $tabParam) {
        $url = $paginator->url($page);
        if ($tabParam) {
            $url .= (str_contains($url, '?') ? '&' : '?') . 'tab=' . $tabParam;
        }
        return $url;
    };
@endphp

@if($isPaginator && $total >= 0)
<div class="flex items-center justify-between">
    <!-- Showing X to Y text -->
    <p class="text-xs text-gray-400" style="font-family: Poppins, sans-serif;">
        Showing {{ $firstItem }} to {{ $lastItem }} of {{ $total }} {{ $recordLabel }}
    </p>
    
    <!-- Custom Pagination -->
    @if($hasPages)
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        
        // Build page numbers to show
        $pages = [];
        
        if ($lastPage <= 7) {
            // Show all pages if 7 or less
            $pages = range(1, $lastPage);
        } else {
            // Always show first page
            $pages[] = 1;
            
            if ($currentPage > 3) {
                $pages[] = '...';
            }
            
            // Pages around current
            $start = max(2, $currentPage - 1);
            $end = min($lastPage - 1, $currentPage + 1);
            
            for ($i = $start; $i <= $end; $i++) {
                if (!in_array($i, $pages)) {
                    $pages[] = $i;
                }
            }
            
            if ($currentPage < $lastPage - 2) {
                $pages[] = '...';
            }
            
            // Always show last page
            if (!in_array($lastPage, $pages)) {
                $pages[] = $lastPage;
            }
        }
    @endphp
    <nav class="flex items-center gap-1" style="font-family: Poppins, sans-serif;">
        <!-- First Page << -->
        <a href="{{ $currentPage == 1 ? '#' : $buildUrl(1) }}" 
           class="flex items-center justify-center w-8 h-8 text-xs rounded {{ $currentPage == 1 ? 'text-gray-300 cursor-not-allowed pointer-events-none' : 'text-gray-500 hover:bg-gray-100' }}"
           @if($currentPage == 1) aria-disabled="true" @endif>
            &laquo;
        </a>
        
        <!-- Previous Page < -->
        <a href="{{ $currentPage == 1 ? '#' : $buildUrl($currentPage - 1) }}" 
           class="flex items-center justify-center w-8 h-8 text-xs rounded {{ $currentPage == 1 ? 'text-gray-300 cursor-not-allowed pointer-events-none' : 'text-gray-500 hover:bg-gray-100' }}"
           @if($currentPage == 1) aria-disabled="true" @endif>
            &lsaquo;
        </a>
        
        <!-- Page Numbers -->
        @foreach($pages as $page)
            @if($page === '...')
                <span class="flex items-center justify-center w-8 h-8 text-xs text-gray-400">..</span>
            @else
                <a href="{{ $buildUrl($page) }}" 
                   class="flex items-center justify-center w-8 h-8 text-xs rounded-full {{ $page == $currentPage ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    {{ $page }}
                </a>
            @endif
        @endforeach
        
        <!-- Next Page > -->
        <a href="{{ $currentPage == $lastPage ? '#' : $buildUrl($currentPage + 1) }}" 
           class="flex items-center justify-center w-8 h-8 text-xs rounded {{ $currentPage == $lastPage ? 'text-gray-300 cursor-not-allowed pointer-events-none' : 'text-gray-500 hover:bg-gray-100' }}"
           @if($currentPage == $lastPage) aria-disabled="true" @endif>
            &rsaquo;
        </a>
        
        <!-- Last Page >> -->
        <a href="{{ $currentPage == $lastPage ? '#' : $buildUrl($lastPage) }}" 
           class="flex items-center justify-center w-8 h-8 text-xs rounded {{ $currentPage == $lastPage ? 'text-gray-300 cursor-not-allowed pointer-events-none' : 'text-gray-500 hover:bg-gray-100' }}"
           @if($currentPage == $lastPage) aria-disabled="true" @endif>
            &raquo;
        </a>
    </nav>
    @endif
</div>
@endif
