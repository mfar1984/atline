@extends('layouts.app')

@section('title', 'Internal Inventory')

@section('page-title', 'Internal Inventory')

@section('content')
<div class="bg-white border border-gray-200">
    <!-- Page Header -->
    <div class="px-6 py-4 flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Office Asset Management</h2>
            <p class="text-xs text-gray-500 mt-0.5">Manage office assets and employee checkouts</p>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="border-t border-gray-200">
        <nav class="flex px-6" aria-label="Tabs">
            @permission('internal_inventory_assets.view')
            <a href="{{ route('internal.inventory.index', ['tab' => 'assets']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'assets' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Asset
            </a>
            @endpermission
            @permission('internal_inventory_movements.view')
            <a href="{{ route('internal.inventory.index', ['tab' => 'movements']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'movements' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Movement
            </a>
            @endpermission
            @permission('internal_inventory_checkout.view')
            <a href="{{ route('internal.inventory.index', ['tab' => 'checkout']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'checkout' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Checkout
            </a>
            @endpermission
            @permission('internal_inventory_locations.view')
            <a href="{{ route('internal.inventory.index', ['tab' => 'locations']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'locations' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Location
            </a>
            @endpermission
            @permission('internal_inventory_brands.view')
            <a href="{{ route('internal.inventory.index', ['tab' => 'brands']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'brands' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Brand
            </a>
            @endpermission
            @permission('internal_inventory_categories.view')
            <a href="{{ route('internal.inventory.index', ['tab' => 'categories']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'categories' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Category
            </a>
            @endpermission
        </nav>
    </div>

    <!-- Tab Content -->
    <div class="px-6 py-4 pb-6 border-t border-gray-200">
        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded text-xs">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded text-xs">
                {{ session('error') }}
            </div>
        @endif

        @if($activeTab === 'assets')
            @include('internal.inventory.partials.assets')
        @elseif($activeTab === 'movements')
            @include('internal.inventory.partials.movements')
        @elseif($activeTab === 'checkout')
            @include('internal.inventory.partials.checkout')
        @elseif($activeTab === 'locations')
            @include('internal.inventory.partials.locations')
        @elseif($activeTab === 'brands')
            @include('internal.inventory.partials.brands')
        @elseif($activeTab === 'categories')
            @include('internal.inventory.partials.categories')
        @endif
    </div>
</div>

@push('scripts')
<script>
function toggleStatus(type, id) {
    fetch(`/internal/inventory/${type}/${id}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to toggle status');
    });
}
</script>
@endpush
@endsection
