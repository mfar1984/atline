@props([
    'editUrl' => null,
    'editOnclick' => null,
    'showUrl' => null,
    'showOnclick' => null,
    'restoreOnclick' => null,
    'deleteOnclick' => null,
    'moreActions' => []
])

@php
    $hasEdit = $editUrl || $editOnclick;
    $hasShow = $showUrl || $showOnclick;
    $hasRestore = $restoreOnclick;
    $hasMore = $deleteOnclick || count($moreActions) > 0;
    
    // Determine border radius for each button based on position
    $editRadius = $hasEdit && !$hasShow && !$hasRestore && !$hasMore ? '9999px' : ($hasEdit ? '9999px 0 0 9999px' : '0');
    $showRadius = $hasShow && !$hasEdit && !$hasRestore && !$hasMore ? '9999px' : ($hasShow && !$hasEdit ? '9999px 0 0 9999px' : ($hasShow && !$hasRestore && !$hasMore ? '0 9999px 9999px 0' : '0'));
    $restoreRadius = $hasRestore && !$hasEdit && !$hasShow && !$hasMore ? '9999px' : ($hasRestore && !$hasEdit && !$hasShow ? '9999px 0 0 9999px' : ($hasRestore && !$hasMore ? '0 9999px 9999px 0' : '0'));
    $moreRadius = $hasMore && !$hasEdit && !$hasShow && !$hasRestore ? '9999px' : ($hasMore ? '0 9999px 9999px 0' : '0');
@endphp

<div class="inline-flex items-center bg-white border border-gray-300 rounded-full shadow-sm" style="border-radius: 9999px !important; padding: 1px;">
    <!-- Edit Button -->
    @if($editUrl)
    <a href="{{ $editUrl }}" 
       class="inline-flex items-center justify-center text-blue-600 hover:text-blue-700 hover:bg-blue-50 transition-colors duration-150"
       title="Edit"
       style="border-radius: {{ $editRadius }} !important; padding: 4px 6px;">
        <span class="material-symbols-outlined" style="font-size: 16px;">edit</span>
    </a>
    @elseif($editOnclick)
    <button type="button" onclick="{{ $editOnclick }}"
       class="inline-flex items-center justify-center text-blue-600 hover:text-blue-700 hover:bg-blue-50 transition-colors duration-150"
       title="Edit"
       style="border-radius: {{ $editRadius }} !important; padding: 4px 6px;">
        <span class="material-symbols-outlined" style="font-size: 16px;">edit</span>
    </button>
    @endif

    <!-- Divider between Edit and Show -->
    @if($hasEdit && $hasShow)
    <div class="h-4 w-px bg-gray-300"></div>
    @endif

    <!-- View Button -->
    @if($showUrl)
    <a href="{{ $showUrl }}" 
       class="inline-flex items-center justify-center text-green-600 hover:text-green-700 hover:bg-green-50 transition-colors duration-150"
       title="View"
       style="border-radius: {{ $showRadius }} !important; padding: 4px 6px;">
        <span class="material-symbols-outlined" style="font-size: 16px;">open_in_new</span>
    </a>
    @elseif($showOnclick)
    <button type="button" onclick="{{ $showOnclick }}"
       class="inline-flex items-center justify-center text-green-600 hover:text-green-700 hover:bg-green-50 transition-colors duration-150"
       title="View"
       style="border-radius: {{ $showRadius }} !important; padding: 4px 6px;">
        <span class="material-symbols-outlined" style="font-size: 16px;">open_in_new</span>
    </button>
    @endif

    <!-- Divider between Show and Restore -->
    @if($hasShow && $hasRestore)
    <div class="h-4 w-px bg-gray-300"></div>
    @endif

    <!-- Restore Button -->
    @if($restoreOnclick)
    <button type="button" onclick="{{ $restoreOnclick }}"
       class="inline-flex items-center justify-center text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 transition-colors duration-150"
       title="Restore"
       style="border-radius: {{ $restoreRadius }} !important; padding: 4px 6px;">
        <span class="material-symbols-outlined" style="font-size: 16px;">restore</span>
    </button>
    @endif

    <!-- Divider before More Actions -->
    @if(($hasEdit || $hasShow || $hasRestore) && $hasMore)
    <div class="h-4 w-px bg-gray-300"></div>
    @endif

    <!-- More Actions Dropdown -->
    @if($hasMore)
    <div class="dropdown-container" style="position: relative; display: inline-block;">
        <button type="button" 
                class="inline-flex items-center justify-center text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-colors duration-150 dropdown-trigger"
                title="More actions"
                style="border-radius: {{ $moreRadius }} !important; padding: 4px 6px;">
            <span class="material-symbols-outlined" style="font-size: 16px;">more_vert</span>
        </button>

        <!-- Dropdown Menu -->
        <div class="dropdown-menu hidden" style="position: fixed !important; z-index: 99999 !important; border-radius: 3px !important; background: white !important; border: 1px solid #e5e7eb !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important; min-width: 8rem !important;">
            <div style="padding: 0.25rem 0;">
                @if($deleteOnclick)
                <button type="button" 
                        onclick="{{ $deleteOnclick }}"
                        class="w-full text-left flex items-center transition-colors duration-150"
                        style="font-family: Poppins, sans-serif !important; font-size: 11px !important; padding: 0.35rem 0.65rem !important; color: #dc2626 !important;"
                        onmouseover="this.style.backgroundColor='#fef2f2'"
                        onmouseout="this.style.backgroundColor='transparent'">
                    <span class="material-symbols-outlined" style="font-size: 14px !important; margin-right: 0.4rem;">delete</span>
                    Delete
                </button>
                @endif

                @foreach($moreActions as $action)
                    @if(isset($action['onclick']))
                    <button type="button"
                       onclick="{{ $action['onclick'] }}"
                       class="w-full text-left flex items-center transition-colors duration-150"
                       style="font-family: Poppins, sans-serif !important; font-size: 11px !important; padding: 0.35rem 0.65rem !important; color: #374151 !important; display: flex !important;"
                       onmouseover="this.style.backgroundColor='#f9fafb'"
                       onmouseout="this.style.backgroundColor='transparent'">
                        @if(isset($action['icon']))
                        <span class="material-symbols-outlined" style="font-size: 14px !important; margin-right: 0.4rem;">{{ $action['icon'] }}</span>
                        @endif
                        {{ $action['label'] }}
                    </button>
                    @else
                    <a href="{{ $action['url'] }}"
                       class="flex items-center transition-colors duration-150"
                       style="font-family: Poppins, sans-serif !important; font-size: 11px !important; padding: 0.35rem 0.65rem !important; color: #374151 !important; display: flex !important;"
                       onmouseover="this.style.backgroundColor='#f9fafb'"
                       onmouseout="this.style.backgroundColor='transparent'">
                        @if(isset($action['icon']))
                        <span class="material-symbols-outlined" style="font-size: 14px !important; margin-right: 0.4rem;">{{ $action['icon'] }}</span>
                        @endif
                        {{ $action['label'] }}
                    </a>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
