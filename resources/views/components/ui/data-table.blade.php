@props([
    'headers' => [],
    'data' => [],
    'emptyMessage' => 'No data found.',
    'actions' => true
])

<div class="data-table-wrapper overflow-x-auto shadow border border-gray-200" style="overflow-y: visible !important;">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                @foreach($headers as $index => $header)
                    <th scope="col" 
                        class="px-6 py-3 {{ $header['align'] ?? 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider {{ $header['width'] ?? '' }} {{ isset($header['hideOnMobile']) && $header['hideOnMobile'] ? 'hide-on-mobile' : '' }} {{ isset($header['hideOnTablet']) && $header['hideOnTablet'] ? 'hide-on-tablet' : '' }}" 
                        style="font-family: Poppins, sans-serif !important; font-size: 11px !important;"
                        data-priority="{{ $header['priority'] ?? $index }}">
                        {{ $header['label'] }}
                    </th>
                @endforeach
                @if($actions)
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider sticky-action-col" style="font-family: Poppins, sans-serif !important; font-size: 11px !important;">
                        Actions
                    </th>
                @endif
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            {{ $slot }}
        </tbody>
    </table>
</div>

<style>
    /* Data table responsive styles */
    @media (max-width: 640px) {
        .data-table-wrapper {
            margin-left: -16px !important;
            margin-right: -16px !important;
            border-left: none !important;
            border-right: none !important;
            border-radius: 0 !important;
        }
        .data-table-wrapper table {
            font-size: 11px !important;
        }
        .data-table-wrapper th,
        .data-table-wrapper td {
            padding: 8px 10px !important;
        }
        .data-table-wrapper th:first-child,
        .data-table-wrapper td:first-child {
            padding-left: 16px !important;
        }
        .data-table-wrapper th:last-child,
        .data-table-wrapper td:last-child {
            padding-right: 16px !important;
        }
        .hide-on-mobile {
            display: none !important;
        }
    }
    
    @media (min-width: 641px) and (max-width: 1024px) {
        .data-table-wrapper th,
        .data-table-wrapper td {
            padding: 10px 12px !important;
        }
        .hide-on-tablet {
            display: none !important;
        }
    }
    
    /* Sticky action column on scroll */
    @media (max-width: 1024px) {
        .sticky-action-col {
            position: sticky;
            right: 0;
            background: inherit;
            z-index: 1;
        }
        .data-table-wrapper tbody tr:hover .sticky-action-col {
            background: #f9fafb;
        }
    }
</style>
