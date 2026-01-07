@extends('layouts.app')

@section('title', 'External Settings')

@section('page-title', 'External Settings')

@section('content')
<div class="bg-white border border-gray-200">
    <!-- Page Header -->
    <div class="px-6 py-4 flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">External Settings</h2>
            <p class="text-xs text-gray-500 mt-0.5">Manage master data for External module</p>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="border-t border-gray-200">
        <nav class="flex px-6" aria-label="Tabs">
            @permission('external_settings_client.view')
            <a href="{{ route('external.settings.index', ['tab' => 'clients']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'clients' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Client
            </a>
            @endpermission
            @permission('external_settings_vendor.view')
            <a href="{{ route('external.settings.index', ['tab' => 'vendors']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'vendors' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Vendor
            </a>
            @endpermission
            @permission('external_settings_location.view')
            <a href="{{ route('external.settings.index', ['tab' => 'locations']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'locations' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Location
            </a>
            @endpermission
            @permission('external_settings_brand.view')
            <a href="{{ route('external.settings.index', ['tab' => 'brands']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'brands' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Brand
            </a>
            @endpermission
            @permission('external_settings_category.view')
            <a href="{{ route('external.settings.index', ['tab' => 'categories']) }}"
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

        @if($activeTab === 'clients')
            @include('external.settings.partials.clients')
        @elseif($activeTab === 'vendors')
            @include('external.settings.partials.vendors')
        @elseif($activeTab === 'locations')
            @include('external.settings.partials.locations')
        @elseif($activeTab === 'brands')
            @include('external.settings.partials.brands')
        @elseif($activeTab === 'categories')
            @include('external.settings.partials.categories')
        @endif
    </div>
</div>

@push('scripts')
<script>
function toggleStatus(type, id, element) {
    fetch(`/external/settings/${type}/${id}/toggle-status`, {
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

// Client functions
function editClient(id, data) {
    window.dispatchEvent(new CustomEvent('edit-client', { detail: { id: id, data: data } }));
}

function deleteClient(id) {
    window.showDeleteModal('{{ route("external.settings.index") }}/clients/' + id);
}

// Vendor functions
function editVendor(id, data) {
    window.dispatchEvent(new CustomEvent('edit-vendor', { detail: { id: id, data: data } }));
}

function deleteVendor(id) {
    window.showDeleteModal('{{ route("external.settings.index") }}/vendors/' + id);
}

// Location functions
function editLocation(id, data) {
    window.dispatchEvent(new CustomEvent('edit-location', { detail: { id: id, data: data } }));
}

function deleteLocation(id) {
    window.showDeleteModal('{{ route("external.settings.index") }}/locations/' + id);
}

// Brand functions
function editBrand(id, data) {
    window.dispatchEvent(new CustomEvent('edit-brand', { detail: { id: id, data: data } }));
}

function deleteBrand(id) {
    window.showDeleteModal('{{ route("external.settings.index") }}/brands/' + id);
}

// Category functions
function editCategory(id, data, fields) {
    window.dispatchEvent(new CustomEvent('edit-category', { detail: { id: id, data: data, fields: fields || [] } }));
}

function deleteCategory(id) {
    window.showDeleteModal('{{ route("external.settings.index") }}/categories/' + id);
}
</script>
@endpush
@endsection
