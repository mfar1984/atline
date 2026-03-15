@extends('layouts.app')

@section('title', 'Create Role')

@section('page-title', 'Create Role')

@section('content')
<div class="bg-white border border-gray-200">
    <!-- Header -->
    <div class="px-6 py-4 flex items-center justify-between border-b border-gray-100">
        <div>
            <h2 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Create New Role</h2>
            <p class="text-xs text-gray-500 mt-0.5" style="font-size: 11px;">Add a new role with permissions</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('settings.roles.index') }}" class="inline-flex items-center gap-2 px-3 bg-gray-500 text-white text-xs font-medium rounded hover:bg-gray-600 transition" style="min-height: 32px; font-family: Poppins, sans-serif; font-size: 11px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">arrow_back</span>
                BACK
            </a>
            <button type="submit" form="role-form" class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px; font-family: Poppins, sans-serif; font-size: 11px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">save</span>
                SAVE
            </button>
        </div>
    </div>

    <!-- Form -->
    <form id="role-form" action="{{ route('settings.roles.store') }}" method="POST">
        @csrf
        <div class="p-6">
            <!-- Role Information -->
            <div class="border border-gray-200 rounded">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Role Information</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">
                                Role Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" required 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 @error('name') border-red-500 @enderror" 
                                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                   value="{{ old('name') }}"
                                   placeholder="Enter role name">
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select name="is_active" required
                                    class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                    style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Description</label>
                            <textarea name="description" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                      style="font-family: Poppins, sans-serif; font-size: 11px;"
                                      placeholder="Enter role description">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permission Matrix -->
            <x-ui.permission-matrix-edit :permissions="[]" />
        </div>
    </form>
</div>
@endsection
