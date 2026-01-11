@extends('layouts.app')

@section('title', 'Helpdesk')

@section('page-title', 'Helpdesk')

@section('content')
<div class="bg-white border border-gray-200" style="overflow: hidden; min-width: 0;">
    <!-- Page Header -->
    <div class="px-6 py-4 flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Support Tickets</h2>
            <p class="text-xs text-gray-500 mt-0.5">
                @if($client)
                    Your support requests for {{ $client->name }}
                @else
                    Manage and track support requests
                @endif
            </p>
        </div>
        @if($activeTab === 'tickets')
        {{-- Client users can always create tickets for their projects --}}
        @if($isClient)
        <div class="flex items-center gap-2">
            <button type="button" onclick="showCreateModal()"
               class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition"
               style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">add_circle</span>
                NEW TICKET
            </button>
        </div>
        @else
        @permission('helpdesk_tickets.create')
        <div class="flex items-center gap-2">
            <button type="button" onclick="showCreateModal()"
               class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition"
               style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">add_circle</span>
                NEW TICKET
            </button>
        </div>
        @endpermission
        @endif
        @endif
    </div>

    <!-- Tabs Navigation -->
    <div class="border-t border-gray-200">
        <nav class="flex px-6" aria-label="Tabs">
            {{-- Client users always see Tickets tab (data is isolated by project access) --}}
            @if($isClient)
            <a href="{{ route('helpdesk.index', ['tab' => 'tickets']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'tickets' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Tickets
            </a>
            @else
            @permission('helpdesk_tickets.view')
            <a href="{{ route('helpdesk.index', ['tab' => 'tickets']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'tickets' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Tickets
            </a>
            @endpermission
            @endif
            @if(!$isClient)
            @permission('helpdesk_templates.view')
            <a href="{{ route('helpdesk.index', ['tab' => 'templates']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'templates' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Ticket Template
            </a>
            @endpermission
            @permission('helpdesk_priorities.view')
            <a href="{{ route('helpdesk.index', ['tab' => 'priorities']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'priorities' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Priority
            </a>
            @endpermission
            @permission('helpdesk_categories.view')
            <a href="{{ route('helpdesk.index', ['tab' => 'categories']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'categories' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Category
            </a>
            @endpermission
            @permission('helpdesk_statuses.view')
            <a href="{{ route('helpdesk.index', ['tab' => 'statuses']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'statuses' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Statuses
            </a>
            @endpermission
            @endif
            {{-- Client users always see Reports tab (data is isolated by project access) --}}
            @if($isClient)
            <a href="{{ route('helpdesk.index', ['tab' => 'reports']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'reports' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Reports
            </a>
            @else
            @permission('helpdesk_reports.view')
            <a href="{{ route('helpdesk.index', ['tab' => 'reports']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'reports' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Reports
            </a>
            @endpermission
            @endif
        </nav>
    </div>

    <!-- Tab Content -->
    <div class="px-6 py-4 pb-6 border-t border-gray-200" style="overflow: hidden; min-width: 0;">
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

        @if($activeTab === 'tickets')
            @include('helpdesk.partials.tickets')
        @elseif($activeTab === 'templates')
            @include('helpdesk.partials.templates')
        @elseif($activeTab === 'priorities')
            @include('helpdesk.partials.priorities')
        @elseif($activeTab === 'categories')
            @include('helpdesk.partials.categories')
        @elseif($activeTab === 'statuses')
            @include('helpdesk.partials.statuses')
        @elseif($activeTab === 'reports')
            @include('helpdesk.partials.reports')
        @endif
    </div>
</div>


<!-- Create Ticket Modal -->
<div id="create-modal" class="fixed inset-0 flex items-center justify-center" style="background-color: rgba(0,0,0,0.5) !important; z-index: 9999 !important; display: none;">
    <div style="background-color: #ffffff !important; border-radius: 12px !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25) !important; width: 100% !important; max-width: 480px !important; margin: 16px !important; overflow: hidden !important;">
        <div style="padding: 16px 20px !important; border-bottom: 1px solid #e5e7eb !important; display: flex !important; align-items: center !important; justify-content: space-between !important; background-color: #f9fafb !important;">
            <div style="display: flex !important; align-items: center !important; gap: 10px !important;">
                <div style="width: 36px !important; height: 36px !important; border-radius: 8px !important; background-color: #3b82f6 !important; display: flex !important; align-items: center !important; justify-content: center !important;">
                    <span class="material-symbols-outlined" style="font-size: 20px !important; color: #ffffff !important;">confirmation_number</span>
                </div>
                <h3 style="font-size: 14px !important; font-weight: 600 !important; color: #111827 !important; font-family: Poppins, sans-serif !important; margin: 0 !important;">Create New Ticket</h3>
            </div>
            <button type="button" onclick="closeCreateModal()" style="width: 32px !important; height: 32px !important; border-radius: 6px !important; border: none !important; background-color: transparent !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important; transition: background-color 0.15s !important;" onmouseover="this.style.backgroundColor='#e5e7eb'" onmouseout="this.style.backgroundColor='transparent'">
                <span class="material-symbols-outlined" style="font-size: 20px !important; color: #6b7280 !important;">close</span>
            </button>
        </div>
        <form id="ticket-form" action="{{ route('helpdesk.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="padding: 20px !important; max-height: 60vh !important; overflow-y: auto !important;">
                <div style="display: flex !important; flex-direction: column !important; gap: 16px !important;">
                @if($client)
                <div id="serial-verify-section">
                    <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">
                        Serial Number <span style="color: #ef4444 !important;">*</span>
                    </label>
                    <input type="text" id="serial-input" 
                           style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                           placeholder="Enter asset serial number"
                           onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                    <p style="font-size: 10px !important; color: #9ca3af !important; margin: 6px 0 12px 0 !important; font-family: Poppins, sans-serif !important;">Enter the serial number of your asset to verify ownership</p>
                    <button type="button" onclick="verifySerial()" id="verify-btn"
                            style="width: 100% !important; padding: 10px 16px !important; background-color: #4b5563 !important; color: #ffffff !important; border: none !important; border-radius: 6px !important; font-size: 11px !important; font-weight: 500 !important; font-family: Poppins, sans-serif !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important; gap: 6px !important;">
                        <span class="material-symbols-outlined" style="font-size: 16px !important;">search</span>
                        VERIFY SERIAL NUMBER
                    </button>
                </div>
                <div id="asset-info" class="hidden">
                    <div style="padding: 12px 16px !important; background-color: #f0fdf4 !important; border: 1px solid #bbf7d0 !important; border-radius: 8px !important;">
                        <div style="display: flex !important; align-items: center !important; gap: 8px !important; margin-bottom: 10px !important;">
                            <span class="material-symbols-outlined" style="font-size: 18px !important; color: #22c55e !important;">check_circle</span>
                            <span style="font-size: 12px !important; font-weight: 600 !important; color: #166534 !important; font-family: Poppins, sans-serif !important;">Asset Verified</span>
                        </div>
                        <div style="display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 8px 16px !important;">
                            <div style="display: grid !important; grid-template-columns: auto 1fr !important; gap: 4px !important; font-size: 11px !important;">
                                <span style="color: #6b7280 !important; white-space: nowrap !important;">Serial:</span>
                                <span id="asset-serial" style="font-weight: 500 !important; color: #1f2937 !important;">-</span>
                            </div>
                            <div style="display: grid !important; grid-template-columns: auto 1fr !important; gap: 4px !important; font-size: 11px !important;">
                                <span style="color: #6b7280 !important; white-space: nowrap !important;">Model:</span>
                                <span id="asset-model" style="font-weight: 500 !important; color: #1f2937 !important;">-</span>
                            </div>
                            <div style="display: grid !important; grid-template-columns: auto 1fr !important; gap: 4px !important; font-size: 11px !important;">
                                <span style="color: #6b7280 !important; white-space: nowrap !important;">Category:</span>
                                <span id="asset-category" style="font-weight: 500 !important; color: #1f2937 !important;">-</span>
                            </div>
                            <div style="display: grid !important; grid-template-columns: auto 1fr !important; gap: 4px !important; font-size: 11px !important;">
                                <span style="color: #6b7280 !important; white-space: nowrap !important;">Project:</span>
                                <span id="asset-project" style="font-weight: 500 !important; color: #1f2937 !important;">-</span>
                            </div>
                        </div>
                        <input type="hidden" name="asset_id" id="asset-id-input">
                    </div>
                    <button type="button" onclick="resetVerification()" style="font-size: 11px !important; color: #3b82f6 !important; background: none !important; border: none !important; cursor: pointer !important; margin-top: 6px !important; font-family: Poppins, sans-serif !important;">Change asset</button>
                </div>
                <div id="verify-error" class="hidden" style="padding: 12px 16px !important; background-color: #fef2f2 !important; border: 1px solid #fecaca !important; border-radius: 8px !important;">
                    <p style="font-size: 12px !important; color: #dc2626 !important; margin: 0 !important; font-family: Poppins, sans-serif !important;" id="verify-error-msg"></p>
                </div>
                @elseif($canAssign)
                <div>
                    <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">
                        Client <span style="color: #ef4444 !important;">*</span>
                    </label>
                    <select name="client_id" required
                            style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; background-color: #ffffff !important; outline: none !important; cursor: pointer !important;">
                        <option value="">Select Client</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                @else
                <div style="padding: 12px 16px !important; background-color: #eff6ff !important; border: 1px solid #bfdbfe !important; border-radius: 8px !important; display: flex !important; align-items: center !important; gap: 10px !important;">
                    <span class="material-symbols-outlined" style="font-size: 18px !important; color: #3b82f6 !important;">info</span>
                    <p style="font-size: 12px !important; color: #1e40af !important; margin: 0 !important; font-family: Poppins, sans-serif !important;">
                        This ticket will be created by you and visible only to you until assigned.
                    </p>
                </div>
                @endif
                </div>

                <div id="ticket-fields" class="{{ $client ? 'hidden' : '' }}">
                    <div style="display: flex !important; flex-direction: column !important; gap: 16px !important;">
                        <div>
                            <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">
                                Subject <span style="color: #ef4444 !important;">*</span>
                            </label>
                            <input type="text" name="subject" required
                                   style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                   placeholder="Brief description of the issue"
                                   onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                        </div>
                        <div style="display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 12px !important;">
                            <!-- Priority Custom Dropdown -->
                            <div>
                                <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">
                                    Priority <span style="color: #ef4444 !important;">*</span>
                                </label>
                                <input type="hidden" name="priority_id" id="priority-input" required value="{{ $activePriorities->where('is_default', true)->first()?->id ?? $activePriorities->first()?->id }}">
                                <div style="position: relative !important;">
                                    <div id="priority-dropdown-btn" onclick="togglePriorityDropdown()" 
                                         style="width: 100% !important; padding: 8px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; background-color: #ffffff !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: space-between !important; min-height: 40px !important; box-sizing: border-box !important;">
                                        @php $defaultPriority = $activePriorities->where('is_default', true)->first() ?? $activePriorities->first(); @endphp
                                        <div style="display: flex !important; align-items: center !important; gap: 8px !important;">
                                            @if($defaultPriority)
                                            <span style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 22px !important; height: 22px !important; border-radius: 4px !important; background-color: {{ $defaultPriority->color }}20 !important;">
                                                <span class="material-symbols-outlined" style="font-size: 14px !important; color: {{ $defaultPriority->color }} !important;">{{ $defaultPriority->icon }}</span>
                                            </span>
                                            <span id="priority-selected-text" style="font-size: 12px !important; color: #1f2937 !important; font-family: Poppins, sans-serif !important;">{{ $defaultPriority->name }}</span>
                                            @else
                                            <span id="priority-selected-text" style="font-size: 12px !important; color: #9ca3af !important; font-family: Poppins, sans-serif !important;">Select Priority</span>
                                            @endif
                                        </div>
                                        <span class="material-symbols-outlined" style="font-size: 18px !important; color: #9ca3af !important;">expand_more</span>
                                    </div>
                                    <div id="priority-dropdown-list" style="display: none !important; position: absolute !important; top: 100% !important; left: 0 !important; right: 0 !important; margin-top: 4px !important; background-color: #ffffff !important; border: 1px solid #e5e7eb !important; border-radius: 6px !important; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1) !important; z-index: 50 !important; max-height: 200px !important; overflow-y: auto !important;">
                                        @foreach($activePriorities as $p)
                                        <div onclick="selectPriority('{{ $p->id }}', '{{ $p->name }}', '{{ $p->color }}', '{{ $p->icon }}')" 
                                             style="padding: 10px 12px !important; cursor: pointer !important; display: flex !important; align-items: center !important; gap: 10px !important; transition: background-color 0.15s !important;"
                                             onmouseover="this.style.backgroundColor='#f3f4f6'" onmouseout="this.style.backgroundColor='#ffffff'">
                                            <span style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 26px !important; height: 26px !important; border-radius: 6px !important; background-color: {{ $p->color }}20 !important;">
                                                <span class="material-symbols-outlined" style="font-size: 16px !important; color: {{ $p->color }} !important;">{{ $p->icon }}</span>
                                            </span>
                                            <span style="font-size: 12px !important; color: #1f2937 !important; font-family: Poppins, sans-serif !important;">{{ $p->name }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <!-- Category Custom Dropdown -->
                            <div>
                                <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">
                                    Category
                                </label>
                                @php $defaultCategory = $activeCategories->where('is_default', true)->first() ?? $activeCategories->first(); @endphp
                                <input type="hidden" name="category_id" id="category-input" value="{{ $defaultCategory?->id }}">
                                <div style="position: relative !important;">
                                    <div id="category-dropdown-btn" onclick="toggleCategoryDropdown()" 
                                         style="width: 100% !important; padding: 8px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; background-color: #ffffff !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: space-between !important; min-height: 40px !important; box-sizing: border-box !important;">
                                        <div id="category-selected-display" style="display: flex !important; align-items: center !important; gap: 8px !important;">
                                            @if($defaultCategory)
                                            <span style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 22px !important; height: 22px !important; border-radius: 4px !important; background-color: {{ $defaultCategory->color }}20 !important;">
                                                <span class="material-symbols-outlined" style="font-size: 14px !important; color: {{ $defaultCategory->color }} !important;">{{ $defaultCategory->icon }}</span>
                                            </span>
                                            <span id="category-selected-text" style="font-size: 12px !important; color: #1f2937 !important; font-family: Poppins, sans-serif !important;">{{ $defaultCategory->name }}</span>
                                            @else
                                            <span id="category-selected-text" style="font-size: 12px !important; color: #9ca3af !important; font-family: Poppins, sans-serif !important;">Select Category</span>
                                            @endif
                                        </div>
                                        <span class="material-symbols-outlined" style="font-size: 18px !important; color: #9ca3af !important;">expand_more</span>
                                    </div>
                                    <div id="category-dropdown-list" style="display: none !important; position: absolute !important; top: 100% !important; left: 0 !important; right: 0 !important; margin-top: 4px !important; background-color: #ffffff !important; border: 1px solid #e5e7eb !important; border-radius: 6px !important; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1) !important; z-index: 50 !important; max-height: 200px !important; overflow-y: auto !important;">
                                        <div onclick="selectCategory('', 'Select Category', '', '')" 
                                             style="padding: 10px 12px !important; cursor: pointer !important; display: flex !important; align-items: center !important; gap: 10px !important; transition: background-color 0.15s !important; color: #9ca3af !important;"
                                             onmouseover="this.style.backgroundColor='#f3f4f6'" onmouseout="this.style.backgroundColor='#ffffff'">
                                            <span style="font-size: 12px !important; font-family: Poppins, sans-serif !important;">-- No Category --</span>
                                        </div>
                                        @foreach($activeCategories as $cat)
                                        <div onclick="selectCategory('{{ $cat->id }}', '{{ $cat->name }}', '{{ $cat->color }}', '{{ $cat->icon }}')" 
                                             style="padding: 10px 12px !important; cursor: pointer !important; display: flex !important; align-items: center !important; gap: 10px !important; transition: background-color 0.15s !important;"
                                             onmouseover="this.style.backgroundColor='#f3f4f6'" onmouseout="this.style.backgroundColor='#ffffff'">
                                            <span style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 26px !important; height: 26px !important; border-radius: 6px !important; background-color: {{ $cat->color }}20 !important;">
                                                <span class="material-symbols-outlined" style="font-size: 16px !important; color: {{ $cat->color }} !important;">{{ $cat->icon }}</span>
                                            </span>
                                            <span style="font-size: 12px !important; color: #1f2937 !important; font-family: Poppins, sans-serif !important;">{{ $cat->name }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">
                                Description <span style="color: #ef4444 !important;">*</span>
                            </label>
                            <textarea name="description" required rows="4"
                                      style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; resize: vertical !important; outline: none !important;"
                                      placeholder="Detailed description of your issue..."
                                      onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'"></textarea>
                        </div>
                        <div>
                            <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">Attachments</label>
                            <div style="position: relative !important;">
                                <input type="file" name="attachments[]" multiple id="ticket-attachments"
                                       style="display: none !important;">
                                <label for="ticket-attachments" 
                                       style="display: flex !important; align-items: center !important; justify-content: center !important; gap: 8px !important; width: 100% !important; padding: 12px !important; border: 2px dashed #d1d5db !important; border-radius: 6px !important; background-color: #f9fafb !important; cursor: pointer !important; transition: all 0.15s !important;"
                                       onmouseover="this.style.borderColor='#3b82f6'; this.style.backgroundColor='#eff6ff'" 
                                       onmouseout="this.style.borderColor='#d1d5db'; this.style.backgroundColor='#f9fafb'">
                                    <span class="material-symbols-outlined" style="font-size: 20px !important; color: #9ca3af !important;">cloud_upload</span>
                                    <span id="attachment-label" style="font-size: 12px !important; color: #6b7280 !important; font-family: Poppins, sans-serif !important;">Click to upload files</span>
                                </label>
                            </div>
                            <p style="font-size: 10px !important; color: #9ca3af !important; margin-top: 4px !important; font-family: Poppins, sans-serif !important;">Max 10MB per file</p>
                        </div>
                    </div>
                </div>
            </div>
            <div style="padding: 16px 20px !important; border-top: 1px solid #e5e7eb !important; display: flex !important; justify-content: flex-end !important; gap: 10px !important; background-color: #f9fafb !important;">
                <button type="button" onclick="closeCreateModal()" 
                        style="padding: 10px 20px !important; font-size: 12px !important; font-weight: 500 !important; color: #374151 !important; background-color: #ffffff !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; cursor: pointer !important; font-family: Poppins, sans-serif !important; transition: all 0.15s !important;"
                        onmouseover="this.style.backgroundColor='#f3f4f6'" onmouseout="this.style.backgroundColor='#ffffff'">
                    Cancel
                </button>
                <button type="submit" id="submit-btn"
                        style="padding: 10px 20px !important; font-size: 12px !important; font-weight: 500 !important; color: #ffffff !important; background-color: #3b82f6 !important; border: none !important; border-radius: 6px !important; cursor: pointer !important; font-family: Poppins, sans-serif !important; display: flex !important; align-items: center !important; gap: 6px !important; transition: all 0.15s !important; {{ $client ? 'opacity: 0.5 !important; cursor: not-allowed !important;' : '' }}"
                        onmouseover="this.style.backgroundColor='#2563eb'" onmouseout="this.style.backgroundColor='#3b82f6'"
                        {{ $client ? 'disabled' : '' }}>
                    <span class="material-symbols-outlined" style="font-size: 16px !important;">send</span>
                    Submit Ticket
                </button>
            </div>
        </form>
    </div>
</div>

<x-modals.delete-confirmation />

@push('scripts')
<script>
// Custom Dropdown Functions
function togglePriorityDropdown() {
    const list = document.getElementById('priority-dropdown-list');
    const categoryList = document.getElementById('category-dropdown-list');
    if (categoryList) categoryList.style.display = 'none';
    list.style.display = list.style.display === 'none' ? 'block' : 'none';
}

function selectPriority(id, name, color, icon) {
    document.getElementById('priority-input').value = id;
    const btn = document.getElementById('priority-dropdown-btn');
    btn.querySelector('div').innerHTML = `
        <span style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 22px !important; height: 22px !important; border-radius: 4px !important; background-color: ${color}20 !important;">
            <span class="material-symbols-outlined" style="font-size: 14px !important; color: ${color} !important;">${icon}</span>
        </span>
        <span style="font-size: 12px !important; color: #1f2937 !important; font-family: Poppins, sans-serif !important;">${name}</span>
    `;
    document.getElementById('priority-dropdown-list').style.display = 'none';
}

function toggleCategoryDropdown() {
    const list = document.getElementById('category-dropdown-list');
    const priorityList = document.getElementById('priority-dropdown-list');
    if (priorityList) priorityList.style.display = 'none';
    list.style.display = list.style.display === 'none' ? 'block' : 'none';
}

function selectCategory(id, name, color, icon) {
    document.getElementById('category-input').value = id;
    const display = document.getElementById('category-selected-display');
    if (id && color && icon) {
        display.innerHTML = `
            <span style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 22px !important; height: 22px !important; border-radius: 4px !important; background-color: ${color}20 !important;">
                <span class="material-symbols-outlined" style="font-size: 14px !important; color: ${color} !important;">${icon}</span>
            </span>
            <span style="font-size: 12px !important; color: #1f2937 !important; font-family: Poppins, sans-serif !important;">${name}</span>
        `;
    } else {
        display.innerHTML = `<span style="font-size: 12px !important; color: #9ca3af !important; font-family: Poppins, sans-serif !important;">Select Category</span>`;
    }
    document.getElementById('category-dropdown-list').style.display = 'none';
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    const priorityBtn = document.getElementById('priority-dropdown-btn');
    const priorityList = document.getElementById('priority-dropdown-list');
    const categoryBtn = document.getElementById('category-dropdown-btn');
    const categoryList = document.getElementById('category-dropdown-list');
    
    if (priorityBtn && priorityList && !priorityBtn.contains(e.target) && !priorityList.contains(e.target)) {
        priorityList.style.display = 'none';
    }
    if (categoryBtn && categoryList && !categoryBtn.contains(e.target) && !categoryList.contains(e.target)) {
        categoryList.style.display = 'none';
    }
});

// File attachment label update
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('ticket-attachments');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const label = document.getElementById('attachment-label');
            const count = e.target.files.length;
            if (count > 0) {
                label.textContent = count + ' file' + (count > 1 ? 's' : '') + ' selected';
            } else {
                label.textContent = 'Click to upload files';
            }
        });
    }
});

function showCreateModal() {
    document.getElementById('create-modal').style.display = 'flex';
    @if($client)
    resetVerification();
    @endif
}

function closeCreateModal() {
    document.getElementById('create-modal').style.display = 'none';
    document.getElementById('ticket-form').reset();
    // Reset custom dropdowns
    const priorityList = document.getElementById('priority-dropdown-list');
    const categoryList = document.getElementById('category-dropdown-list');
    if (priorityList) priorityList.style.display = 'none';
    if (categoryList) categoryList.style.display = 'none';
    // Reset category display
    const categoryDisplay = document.getElementById('category-selected-display');
    if (categoryDisplay) {
        categoryDisplay.innerHTML = `<span style="font-size: 12px !important; color: #9ca3af !important; font-family: Poppins, sans-serif !important;">Select Category</span>`;
    }
    document.getElementById('category-input').value = '';
    // Reset attachment label
    const attachmentLabel = document.getElementById('attachment-label');
    if (attachmentLabel) attachmentLabel.textContent = 'Click to upload files';
    @if($client)
    resetVerification();
    @endif
}

function deleteTicket(id) {
    window.showDeleteModal(`/helpdesk/${id}`);
}

@if($client)
function verifySerial() {
    const serialInput = document.getElementById('serial-input');
    const serialNumber = serialInput.value.trim();
    
    if (!serialNumber) {
        showVerifyError('Please enter a serial number');
        return;
    }

    const verifyBtn = document.getElementById('verify-btn');
    const originalText = verifyBtn.innerHTML;
    verifyBtn.innerHTML = '<span class="material-symbols-outlined animate-spin" style="font-size: 14px;">progress_activity</span> VERIFYING...';
    verifyBtn.disabled = true;

    fetch('{{ route("helpdesk.verify-serial") }}', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ serial_number: serialNumber })
    })
    .then(response => response.json())
    .then(data => {
        verifyBtn.innerHTML = originalText;
        verifyBtn.disabled = false;

        if (data.valid) {
            document.getElementById('serial-verify-section').classList.add('hidden');
            document.getElementById('asset-info').classList.remove('hidden');
            document.getElementById('verify-error').classList.add('hidden');
            
            document.getElementById('asset-serial').textContent = data.asset.serial_number;
            document.getElementById('asset-model').textContent = data.asset.model || data.asset.brand || '-';
            document.getElementById('asset-category').textContent = data.asset.category;
            document.getElementById('asset-project').textContent = data.asset.project;
            document.getElementById('asset-id-input').value = data.asset.id;
            
            document.getElementById('ticket-fields').classList.remove('hidden');
            document.getElementById('submit-btn').disabled = false;
            document.getElementById('submit-btn').style.opacity = '1';
            document.getElementById('submit-btn').style.cursor = 'pointer';
        } else {
            showVerifyError(data.message);
        }
    })
    .catch(error => {
        verifyBtn.innerHTML = originalText;
        verifyBtn.disabled = false;
        showVerifyError('An error occurred. Please try again.');
        console.error('Error:', error);
    });
}

function showVerifyError(message) {
    const errorDiv = document.getElementById('verify-error');
    document.getElementById('verify-error-msg').textContent = message;
    errorDiv.classList.remove('hidden');
}

function resetVerification() {
    document.getElementById('serial-verify-section').classList.remove('hidden');
    document.getElementById('asset-info').classList.add('hidden');
    document.getElementById('verify-error').classList.add('hidden');
    document.getElementById('ticket-fields').classList.add('hidden');
    document.getElementById('serial-input').value = '';
    document.getElementById('asset-id-input').value = '';
    document.getElementById('submit-btn').disabled = true;
    document.getElementById('submit-btn').style.opacity = '0.5';
    document.getElementById('submit-btn').style.cursor = 'not-allowed';
}
@endif

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCreateModal();
    }
});

document.getElementById('create-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCreateModal();
    }
});
</script>
@endpush
@endsection
