@extends('layouts.app')

@section('title', 'Ticket #' . $ticket->ticket_number)

@section('page-title', 'Ticket Details')

@section('content')
<div class="bg-white border border-gray-200">
    <!-- Header -->
    <div class="px-6 py-4 flex items-center justify-between border-b border-gray-200">
        <div class="flex items-center gap-4">
            @php
                $priorityStyles = match($ticket->priority) {
                    'low' => 'background-color: #f3f4f6 !important; color: #4b5563 !important;',
                    'medium' => 'background-color: #dbeafe !important; color: #2563eb !important;',
                    'high' => 'background-color: #ffedd5 !important; color: #ea580c !important;',
                    'urgent' => 'background-color: #fee2e2 !important; color: #dc2626 !important;',
                    default => 'background-color: #f3f4f6 !important; color: #4b5563 !important;',
                };
                $statusStyles = match($ticket->status) {
                    'open' => 'background-color: #dbeafe !important; color: #2563eb !important;',
                    'in_progress' => 'background-color: #fef3c7 !important; color: #d97706 !important;',
                    'pending' => 'background-color: #ffedd5 !important; color: #ea580c !important;',
                    'resolved' => 'background-color: #dcfce7 !important; color: #16a34a !important;',
                    'closed' => 'background-color: #f3f4f6 !important; color: #4b5563 !important;',
                    default => 'background-color: #f3f4f6 !important; color: #4b5563 !important;',
                };
            @endphp
            <div>
                <h2 class="text-base font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">
                    {{ $ticket->ticket_number }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">{{ $ticket->subject }}</p>
            </div>
        </div>
        <a href="{{ route('helpdesk.index') }}" 
           class="inline-flex items-center gap-1 px-3 text-gray-600 text-xs font-medium rounded border border-gray-300 hover:bg-gray-50 transition"
           style="min-height: 32px;">
            <span class="material-symbols-outlined" style="font-size: 14px;">arrow_back</span>
            BACK
        </a>
    </div>

    @if(session('success'))
    <div class="px-6 pt-4">
        <div class="px-4 py-3 bg-green-50 border border-green-200 rounded">
            <p class="text-xs text-green-800" style="font-family: Poppins, sans-serif;">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    <div style="padding: 24px !important;">
        <!-- Info Cards Row - Linked Asset & Ticket Information Side by Side -->
        <div style="display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 20px !important; margin-bottom: 24px !important;">
            <!-- Linked Asset -->
            @if($ticket->asset)
            <div style="border: 1px solid #e5e7eb !important; border-radius: 8px !important; background-color: #ffffff !important; overflow: hidden !important;">
                <div style="padding: 14px 16px !important; background-color: #f0f9ff !important; border-bottom: 1px solid #bae6fd !important; display: flex !important; align-items: center !important; gap: 10px !important;">
                    <div style="width: 36px !important; height: 36px !important; border-radius: 8px !important; background-color: #0ea5e9 !important; display: flex !important; align-items: center !important; justify-content: center !important;">
                        <span class="material-symbols-outlined" style="font-size: 20px !important; color: #ffffff !important;">devices</span>
                    </div>
                    <h4 style="font-size: 13px !important; font-weight: 600 !important; color: #0c4a6e !important; font-family: Poppins, sans-serif !important; margin: 0 !important;">Linked Asset</h4>
                </div>
                <div style="padding: 16px !important;">
                    <div style="display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 16px !important;">
                        <div style="display: flex !important; align-items: flex-start !important; gap: 12px !important;">
                            <div style="width: 32px !important; height: 32px !important; border-radius: 6px !important; background-color: #f1f5f9 !important; display: flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0 !important;">
                                <span class="material-symbols-outlined" style="font-size: 18px !important; color: #64748b !important;">tag</span>
                            </div>
                            <div style="flex: 1 !important;">
                                <p style="font-size: 10px !important; color: #94a3b8 !important; margin: 0 0 2px 0 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important;">Serial Number</p>
                                <p style="font-size: 12px !important; font-weight: 500 !important; color: #1e293b !important; margin: 0 !important; font-family: Poppins, sans-serif !important;">{{ $ticket->asset->serial_number }}</p>
                            </div>
                        </div>
                        @if($ticket->asset->model)
                        <div style="display: flex !important; align-items: flex-start !important; gap: 12px !important;">
                            <div style="width: 32px !important; height: 32px !important; border-radius: 6px !important; background-color: #f1f5f9 !important; display: flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0 !important;">
                                <span class="material-symbols-outlined" style="font-size: 18px !important; color: #64748b !important;">inventory_2</span>
                            </div>
                            <div style="flex: 1 !important;">
                                <p style="font-size: 10px !important; color: #94a3b8 !important; margin: 0 0 2px 0 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important;">Model</p>
                                <p style="font-size: 12px !important; font-weight: 500 !important; color: #1e293b !important; margin: 0 !important; font-family: Poppins, sans-serif !important;">{{ $ticket->asset->model }}</p>
                            </div>
                        </div>
                        @endif
                        @if($ticket->asset->category)
                        <div style="display: flex !important; align-items: flex-start !important; gap: 12px !important;">
                            <div style="width: 32px !important; height: 32px !important; border-radius: 6px !important; background-color: #f1f5f9 !important; display: flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0 !important;">
                                <span class="material-symbols-outlined" style="font-size: 18px !important; color: #64748b !important;">category</span>
                            </div>
                            <div style="flex: 1 !important;">
                                <p style="font-size: 10px !important; color: #94a3b8 !important; margin: 0 0 2px 0 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important;">Category</p>
                                <p style="font-size: 12px !important; font-weight: 500 !important; color: #1e293b !important; margin: 0 !important; font-family: Poppins, sans-serif !important;">{{ $ticket->asset->category->name }}</p>
                            </div>
                        </div>
                        @endif
                        @if($ticket->asset->brand)
                        <div style="display: flex !important; align-items: flex-start !important; gap: 12px !important;">
                            <div style="width: 32px !important; height: 32px !important; border-radius: 6px !important; background-color: #f1f5f9 !important; display: flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0 !important;">
                                <span class="material-symbols-outlined" style="font-size: 18px !important; color: #64748b !important;">business</span>
                            </div>
                            <div style="flex: 1 !important;">
                                <p style="font-size: 10px !important; color: #94a3b8 !important; margin: 0 0 2px 0 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important;">Brand</p>
                                <p style="font-size: 12px !important; font-weight: 500 !important; color: #1e293b !important; margin: 0 !important; font-family: Poppins, sans-serif !important;">{{ $ticket->asset->brand->name }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @else
            <div style="border: 1px solid #e5e7eb !important; border-radius: 8px !important; background-color: #ffffff !important; overflow: hidden !important;">
                <div style="padding: 14px 16px !important; background-color: #f0f9ff !important; border-bottom: 1px solid #bae6fd !important; display: flex !important; align-items: center !important; gap: 10px !important;">
                    <div style="width: 36px !important; height: 36px !important; border-radius: 8px !important; background-color: #0ea5e9 !important; display: flex !important; align-items: center !important; justify-content: center !important;">
                        <span class="material-symbols-outlined" style="font-size: 20px !important; color: #ffffff !important;">devices</span>
                    </div>
                    <h4 style="font-size: 13px !important; font-weight: 600 !important; color: #0c4a6e !important; font-family: Poppins, sans-serif !important; margin: 0 !important;">Linked Asset</h4>
                </div>
                <div style="padding: 32px 16px !important; text-align: center !important;">
                    <span class="material-symbols-outlined" style="font-size: 40px !important; color: #cbd5e1 !important;">link_off</span>
                    <p style="font-size: 12px !important; color: #94a3b8 !important; margin: 8px 0 0 0 !important; font-family: Poppins, sans-serif !important;">No asset linked</p>
                </div>
            </div>
            @endif

            <!-- Ticket Information -->
            <div style="border: 1px solid #e5e7eb !important; border-radius: 8px !important; background-color: #ffffff !important; overflow: hidden !important;">
                <div style="padding: 14px 16px !important; background-color: #f0fdf4 !important; border-bottom: 1px solid #bbf7d0 !important; display: flex !important; align-items: center !important; gap: 10px !important;">
                    <div style="width: 36px !important; height: 36px !important; border-radius: 8px !important; background-color: #22c55e !important; display: flex !important; align-items: center !important; justify-content: center !important;">
                        <span class="material-symbols-outlined" style="font-size: 20px !important; color: #ffffff !important;">info</span>
                    </div>
                    <h4 style="font-size: 13px !important; font-weight: 600 !important; color: #166534 !important; font-family: Poppins, sans-serif !important; margin: 0 !important;">Ticket Information</h4>
                </div>
                <div style="padding: 16px !important;">
                    <div style="display: grid !important; grid-template-columns: 1fr 1fr 1fr !important; gap: 16px !important;">
                        <div style="display: flex !important; align-items: flex-start !important; gap: 12px !important;">
                            <div style="width: 32px !important; height: 32px !important; border-radius: 6px !important; background-color: #f1f5f9 !important; display: flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0 !important;">
                                <span class="material-symbols-outlined" style="font-size: 18px !important; color: #64748b !important;">confirmation_number</span>
                            </div>
                            <div style="flex: 1 !important;">
                                <p style="font-size: 10px !important; color: #94a3b8 !important; margin: 0 0 2px 0 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important;">Ticket Number</p>
                                <p style="font-size: 12px !important; font-weight: 500 !important; color: #1e293b !important; margin: 0 !important; font-family: Poppins, sans-serif !important;">{{ $ticket->ticket_number }}</p>
                            </div>
                        </div>
                        @if($ticket->client)
                        <div style="display: flex !important; align-items: flex-start !important; gap: 12px !important;">
                            <div style="width: 32px !important; height: 32px !important; border-radius: 6px !important; background-color: #f1f5f9 !important; display: flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0 !important;">
                                <span class="material-symbols-outlined" style="font-size: 18px !important; color: #64748b !important;">apartment</span>
                            </div>
                            <div style="flex: 1 !important;">
                                <p style="font-size: 10px !important; color: #94a3b8 !important; margin: 0 0 2px 0 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important;">Client</p>
                                <p style="font-size: 12px !important; font-weight: 500 !important; color: #1e293b !important; margin: 0 !important; font-family: Poppins, sans-serif !important;">{{ $ticket->client->name }}</p>
                            </div>
                        </div>
                        @endif
                        <div style="display: flex !important; align-items: flex-start !important; gap: 12px !important;">
                            <div style="width: 32px !important; height: 32px !important; border-radius: 6px !important; background-color: #f1f5f9 !important; display: flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0 !important;">
                                <span class="material-symbols-outlined" style="font-size: 18px !important; color: #64748b !important;">flag</span>
                            </div>
                            <div style="flex: 1 !important;">
                                <p style="font-size: 10px !important; color: #94a3b8 !important; margin: 0 0 2px 0 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important;">Priority</p>
                                @if($ticket->ticketPriority)
                                <span style="display: inline-flex !important; align-items: center !important; gap: 4px !important; padding: 3px 10px !important; font-size: 11px !important; font-weight: 500 !important; border-radius: 4px !important; font-family: Poppins, sans-serif !important; background-color: {{ $ticket->ticketPriority->color }}20 !important; color: {{ $ticket->ticketPriority->color }} !important;">
                                    <span class="material-symbols-outlined" style="font-size: 14px !important;">{{ $ticket->ticketPriority->icon }}</span>
                                    {{ $ticket->ticketPriority->name }}
                                </span>
                                @else
                                <span style="display: inline-flex !important; padding: 3px 10px !important; font-size: 11px !important; font-weight: 500 !important; border-radius: 4px !important; font-family: Poppins, sans-serif !important; {{ $priorityStyles }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                                @endif
                            </div>
                        </div>
                        <div style="display: flex !important; align-items: flex-start !important; gap: 12px !important;">
                            <div style="width: 32px !important; height: 32px !important; border-radius: 6px !important; background-color: #f1f5f9 !important; display: flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0 !important;">
                                <span class="material-symbols-outlined" style="font-size: 18px !important; color: #64748b !important;">schedule</span>
                            </div>
                            <div style="flex: 1 !important;">
                                <p style="font-size: 10px !important; color: #94a3b8 !important; margin: 0 0 2px 0 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important;">Status</p>
                                @if($ticket->ticketStatus)
                                <span style="display: inline-flex !important; align-items: center !important; gap: 4px !important; padding: 3px 10px !important; font-size: 11px !important; font-weight: 500 !important; border-radius: 4px !important; font-family: Poppins, sans-serif !important; background-color: {{ $ticket->ticketStatus->color }}20 !important; color: {{ $ticket->ticketStatus->color }} !important;">
                                    <span class="material-symbols-outlined" style="font-size: 14px !important;">{{ $ticket->ticketStatus->icon }}</span>
                                    {{ $ticket->ticketStatus->name }}
                                </span>
                                @else
                                <span style="display: inline-flex !important; padding: 3px 10px !important; font-size: 11px !important; font-weight: 500 !important; border-radius: 4px !important; font-family: Poppins, sans-serif !important; {{ $statusStyles }}">
                                    {{ str_replace('_', ' ', ucfirst($ticket->status)) }}
                                </span>
                                @endif
                            </div>
                        </div>
                        @if($ticket->ticketCategory || $ticket->category)
                        <div style="display: flex !important; align-items: flex-start !important; gap: 12px !important;">
                            <div style="width: 32px !important; height: 32px !important; border-radius: 6px !important; background-color: #f1f5f9 !important; display: flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0 !important;">
                                <span class="material-symbols-outlined" style="font-size: 18px !important; color: #64748b !important;">label</span>
                            </div>
                            <div style="flex: 1 !important;">
                                <p style="font-size: 10px !important; color: #94a3b8 !important; margin: 0 0 2px 0 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important;">Category</p>
                                @if($ticket->ticketCategory)
                                <span style="display: inline-flex !important; align-items: center !important; gap: 4px !important; padding: 3px 10px !important; font-size: 11px !important; font-weight: 500 !important; border-radius: 4px !important; font-family: Poppins, sans-serif !important; background-color: {{ $ticket->ticketCategory->color }}20 !important; color: {{ $ticket->ticketCategory->color }} !important;">
                                    <span class="material-symbols-outlined" style="font-size: 14px !important;">{{ $ticket->ticketCategory->icon }}</span>
                                    {{ $ticket->ticketCategory->name }}
                                </span>
                                @else
                                <span style="display: inline-flex !important; padding: 3px 10px !important; font-size: 11px !important; font-weight: 500 !important; border-radius: 4px !important; background-color: #f3e8ff !important; color: #7c3aed !important; font-family: Poppins, sans-serif !important;">
                                    {{ ucfirst($ticket->category) }}
                                </span>
                                @endif
                            </div>
                        </div>
                        @endif
                        <div style="display: flex !important; align-items: flex-start !important; gap: 12px !important;">
                            <div style="width: 32px !important; height: 32px !important; border-radius: 6px !important; background-color: #f1f5f9 !important; display: flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0 !important;">
                                <span class="material-symbols-outlined" style="font-size: 18px !important; color: #64748b !important;">calendar_today</span>
                            </div>
                            <div style="flex: 1 !important;">
                                <p style="font-size: 10px !important; color: #94a3b8 !important; margin: 0 0 2px 0 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important;">Created</p>
                                <p style="font-size: 12px !important; font-weight: 500 !important; color: #1e293b !important; margin: 0 !important; font-family: Poppins, sans-serif !important;">@formatDateTime($ticket->created_at)</p>
                            </div>
                        </div>
                        @if($ticket->resolved_at)
                        <div style="display: flex !important; align-items: flex-start !important; gap: 12px !important;">
                            <div style="width: 32px !important; height: 32px !important; border-radius: 6px !important; background-color: #dcfce7 !important; display: flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0 !important;">
                                <span class="material-symbols-outlined" style="font-size: 18px !important; color: #22c55e !important;">check_circle</span>
                            </div>
                            <div style="flex: 1 !important;">
                                <p style="font-size: 10px !important; color: #94a3b8 !important; margin: 0 0 2px 0 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important;">Resolved</p>
                                <p style="font-size: 12px !important; font-weight: 500 !important; color: #1e293b !important; margin: 0 !important; font-family: Poppins, sans-serif !important;">@formatDateTime($ticket->resolved_at)</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div style="display: grid !important; grid-template-columns: 2fr 1fr !important; gap: 24px !important;">
            <!-- Chat Messages Column - Fixed Height Container -->
            <div style="display: flex !important; flex-direction: column !important; height: 600px !important; border: 1px solid #e5e7eb !important; border-radius: 8px !important; background-color: #ffffff !important; overflow: hidden !important;">
                
                <!-- Scrollable Chat Messages Area -->
                <div id="chat-messages" style="flex: 1 !important; overflow-y: auto !important; padding: 16px !important; display: flex !important; flex-direction: column !important; gap: 16px !important;">
            <!-- Original Ticket (Client) -->
            <div class="border rounded" style="border-color: #bfdbfe !important;">
                <div class="flex items-center justify-between" style="padding: 12px 16px; background-color: #dbeafe !important; border-bottom: 1px solid #bfdbfe !important;">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center" style="width: 32px; height: 32px; border-radius: 50%; background-color: #3b82f6 !important;">
                            <span class="material-symbols-outlined" style="font-size: 16px; color: #ffffff !important;">person</span>
                        </div>
                        <div>
                            <p class="text-xs font-medium" style="font-family: Poppins, sans-serif; color: #1e40af !important;">{{ $ticket->creator->name }}</p>
                            <p class="text-xs" style="color: #3b82f6 !important;">@formatDateTime($ticket->created_at)</p>
                        </div>
                    </div>
                    @if($ticket->category)
                    <span class="inline-flex items-center rounded" style="padding: 2px 8px; font-size: 10px; font-weight: 500; background-color: #f3e8ff !important; color: #7c3aed !important;">
                        {{ ucfirst($ticket->category) }}
                    </span>
                    @endif
                </div>
                <div class="p-4">
                    <div class="text-xs text-gray-700" style="font-family: Poppins, sans-serif; white-space: pre-wrap;">{{ $ticket->description }}</div>
                    
                    @if($ticket->attachments->count() > 0)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs font-medium text-gray-700 mb-2" style="font-family: Poppins, sans-serif;">Attachments:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($ticket->attachments as $attachment)
                            <a href="{{ route('helpdesk.attachment.download', $attachment) }}" 
                               class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 rounded text-xs text-gray-600 hover:bg-gray-200 transition">
                                <span class="material-symbols-outlined" style="font-size: 14px;">attach_file</span>
                                {{ $attachment->original_filename }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Replies -->
            @foreach($ticket->replies as $reply)
            @if(!$reply->is_internal_note || !$client)
            @php
                // Check if reply is from client (ticket creator) or staff
                $isFromClient = $reply->user_id === $ticket->created_by;
                $isInternalNote = $reply->is_internal_note;
            @endphp
            @if($isInternalNote)
            {{-- Internal Note - Yellow --}}
            <div class="border rounded" style="background-color: #fefce8 !important; border-color: #fde047 !important;">
                <div class="flex items-center justify-between" style="padding: 12px 16px; background-color: #fef08a !important; border-bottom: 1px solid #fde047 !important;">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center" style="width: 32px; height: 32px; border-radius: 50%; background-color: #eab308 !important;">
                            <span class="material-symbols-outlined" style="font-size: 16px; color: #ffffff !important;">lock</span>
                        </div>
                        <div>
                            <p class="text-xs font-medium" style="font-family: Poppins, sans-serif; color: #854d0e !important;">{{ $reply->user->name }}</p>
                            <p class="text-xs" style="color: #a16207 !important;">@formatDateTime($reply->created_at)</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center rounded" style="padding: 2px 8px; font-size: 10px; font-weight: 500; background-color: #fde047 !important; color: #854d0e !important;">
                        Internal Note
                    </span>
                </div>
                <div class="p-4">
                    <div class="text-xs text-gray-700" style="font-family: Poppins, sans-serif; white-space: pre-wrap;">{{ $reply->message }}</div>
                    @if($reply->attachments->count() > 0)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs font-medium text-gray-700 mb-2" style="font-family: Poppins, sans-serif;">Attachments:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($reply->attachments as $attachment)
                            <a href="{{ route('helpdesk.attachment.download', $attachment) }}" 
                               class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 rounded text-xs text-gray-600 hover:bg-gray-200 transition">
                                <span class="material-symbols-outlined" style="font-size: 14px;">attach_file</span>
                                {{ $attachment->original_filename }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @elseif($isFromClient)
            {{-- Client Reply - Blue --}}
            @php
                // Calculate SLA Reply time - time from previous message to this reply
                $previousTime = $loop->first ? $ticket->created_at : $ticket->replies[$loop->index - 1]->created_at;
                $slaSeconds = abs($previousTime->diffInSeconds($reply->created_at));
                $slaHours = floor($slaSeconds / 3600);
                $slaMinutes = floor(($slaSeconds % 3600) / 60);
                $slaSecs = $slaSeconds % 60;
                $slaFormatted = sprintf('%02d:%02d:%02d', $slaHours, $slaMinutes, $slaSecs);
                
                // Calculate Total SLA - time from ticket created to this reply
                $totalSlaSeconds = abs($ticket->created_at->diffInSeconds($reply->created_at));
                $totalSlaHours = floor($totalSlaSeconds / 3600);
                $totalSlaMinutes = floor(($totalSlaSeconds % 3600) / 60);
                $totalSlaSecs = $totalSlaSeconds % 60;
                $totalSlaFormatted = sprintf('%02d:%02d:%02d', $totalSlaHours, $totalSlaMinutes, $totalSlaSecs);
            @endphp
            <div class="border rounded" style="border-color: #bfdbfe !important;">
                <div class="flex items-center justify-between" style="padding: 12px 16px; background-color: #dbeafe !important; border-bottom: 1px solid #bfdbfe !important;">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center" style="width: 32px; height: 32px; border-radius: 50%; background-color: #3b82f6 !important;">
                            <span class="material-symbols-outlined" style="font-size: 16px; color: #ffffff !important;">person</span>
                        </div>
                        <div>
                            <p class="text-xs font-medium" style="font-family: Poppins, sans-serif; color: #1e40af !important;">{{ $reply->user->name }}</p>
                            <p class="text-xs" style="color: #3b82f6 !important;">@formatDateTime($reply->created_at)</p>
                        </div>
                    </div>
                    <!-- SLA Times -->
                    <div style="display: flex !important; align-items: center !important; gap: 8px !important;">
                        <!-- SLA Reply Time -->
                        <div style="display: flex !important; align-items: center !important; gap: 4px !important; padding: 4px 8px !important; background-color: #fef3c7 !important; border: 1px solid #fcd34d !important; border-radius: 6px !important;">
                            <span class="material-symbols-outlined" style="font-size: 12px !important; color: #d97706 !important;">schedule</span>
                            <span style="font-size: 9px !important; color: #92400e !important; font-family: Poppins, sans-serif !important; font-weight: 500 !important;">Reply:</span>
                            <span style="font-size: 10px !important; color: #b45309 !important; font-family: 'Courier New', monospace !important; font-weight: 600 !important;">{{ $slaFormatted }}</span>
                        </div>
                        <!-- Total SLA -->
                        <div style="display: flex !important; align-items: center !important; gap: 4px !important; padding: 4px 8px !important; background-color: #fee2e2 !important; border: 1px solid #fca5a5 !important; border-radius: 6px !important;">
                            <span class="material-symbols-outlined" style="font-size: 12px !important; color: #dc2626 !important;">hourglass_top</span>
                            <span style="font-size: 9px !important; color: #991b1b !important; font-family: Poppins, sans-serif !important; font-weight: 500 !important;">Total:</span>
                            <span style="font-size: 10px !important; color: #b91c1c !important; font-family: 'Courier New', monospace !important; font-weight: 600 !important;">{{ $totalSlaFormatted }}</span>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <div class="text-xs text-gray-700" style="font-family: Poppins, sans-serif; white-space: pre-wrap;">{{ $reply->message }}</div>
                    @if($reply->attachments->count() > 0)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs font-medium text-gray-700 mb-2" style="font-family: Poppins, sans-serif;">Attachments:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($reply->attachments as $attachment)
                            <a href="{{ route('helpdesk.attachment.download', $attachment) }}" 
                               class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 rounded text-xs text-gray-600 hover:bg-gray-200 transition">
                                <span class="material-symbols-outlined" style="font-size: 14px;">attach_file</span>
                                {{ $attachment->original_filename }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @else
            {{-- Staff Reply - Green --}}
            @php
                // Calculate SLA Reply time - time from previous message to this reply
                $previousTime = $loop->first ? $ticket->created_at : $ticket->replies[$loop->index - 1]->created_at;
                $slaSeconds = abs($previousTime->diffInSeconds($reply->created_at));
                $slaHours = floor($slaSeconds / 3600);
                $slaMinutes = floor(($slaSeconds % 3600) / 60);
                $slaSecs = $slaSeconds % 60;
                $slaFormatted = sprintf('%02d:%02d:%02d', $slaHours, $slaMinutes, $slaSecs);
                
                // Calculate Total SLA - time from ticket created to this reply
                $totalSlaSeconds = abs($ticket->created_at->diffInSeconds($reply->created_at));
                $totalSlaHours = floor($totalSlaSeconds / 3600);
                $totalSlaMinutes = floor(($totalSlaSeconds % 3600) / 60);
                $totalSlaSecs = $totalSlaSeconds % 60;
                $totalSlaFormatted = sprintf('%02d:%02d:%02d', $totalSlaHours, $totalSlaMinutes, $totalSlaSecs);
            @endphp
            <div class="border rounded" style="border-color: #86efac !important;">
                <div class="flex items-center justify-between" style="padding: 12px 16px; background-color: #dcfce7 !important; border-bottom: 1px solid #86efac !important;">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center" style="width: 32px; height: 32px; border-radius: 50%; background-color: #22c55e !important;">
                            <span class="material-symbols-outlined" style="font-size: 16px; color: #ffffff !important;">support_agent</span>
                        </div>
                        <div>
                            <p class="text-xs font-medium" style="font-family: Poppins, sans-serif; color: #166534 !important;">{{ $reply->user->name }}</p>
                            <p class="text-xs" style="color: #16a34a !important;">@formatDateTime($reply->created_at)</p>
                        </div>
                    </div>
                    <!-- SLA Times -->
                    <div style="display: flex !important; align-items: center !important; gap: 8px !important;">
                        <!-- SLA Reply Time -->
                        <div style="display: flex !important; align-items: center !important; gap: 4px !important; padding: 4px 8px !important; background-color: #fef3c7 !important; border: 1px solid #fcd34d !important; border-radius: 6px !important;">
                            <span class="material-symbols-outlined" style="font-size: 12px !important; color: #d97706 !important;">schedule</span>
                            <span style="font-size: 9px !important; color: #92400e !important; font-family: Poppins, sans-serif !important; font-weight: 500 !important;">Reply:</span>
                            <span style="font-size: 10px !important; color: #b45309 !important; font-family: 'Courier New', monospace !important; font-weight: 600 !important;">{{ $slaFormatted }}</span>
                        </div>
                        <!-- Total SLA -->
                        <div style="display: flex !important; align-items: center !important; gap: 4px !important; padding: 4px 8px !important; background-color: #fee2e2 !important; border: 1px solid #fca5a5 !important; border-radius: 6px !important;">
                            <span class="material-symbols-outlined" style="font-size: 12px !important; color: #dc2626 !important;">hourglass_top</span>
                            <span style="font-size: 9px !important; color: #991b1b !important; font-family: Poppins, sans-serif !important; font-weight: 500 !important;">Total:</span>
                            <span style="font-size: 10px !important; color: #b91c1c !important; font-family: 'Courier New', monospace !important; font-weight: 600 !important;">{{ $totalSlaFormatted }}</span>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <div class="text-xs text-gray-700" style="font-family: Poppins, sans-serif; white-space: pre-wrap;">{{ $reply->message }}</div>
                    @if($reply->attachments->count() > 0)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs font-medium text-gray-700 mb-2" style="font-family: Poppins, sans-serif;">Attachments:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($reply->attachments as $attachment)
                            <a href="{{ route('helpdesk.attachment.download', $attachment) }}" 
                               class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 rounded text-xs text-gray-600 hover:bg-gray-200 transition">
                                <span class="material-symbols-outlined" style="font-size: 14px;">attach_file</span>
                                {{ $attachment->original_filename }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
            @endif
            @endforeach
                </div>
                <!-- End Scrollable Chat Messages Area -->

                <!-- Reply Form - Sticky at Bottom -->
                @if($ticket->status !== 'closed')
                <div style="border-top: 1px solid #e5e7eb !important; background-color: #f9fafb !important; flex-shrink: 0 !important;">
                    <div style="padding: 12px 16px !important; border-bottom: 1px solid #e5e7eb !important; display: flex !important; align-items: center !important; justify-content: space-between !important;">
                        <div style="display: flex !important; align-items: center !important; gap: 16px !important;">
                            <h4 style="font-size: 12px !important; font-weight: 600 !important; color: #374151 !important; font-family: Poppins, sans-serif !important; margin: 0 !important;">Add Reply</h4>
                            
                            <!-- Time Worked & SLA Timer -->
                            <div style="display: flex !important; align-items: center !important; gap: 12px !important;">
                                <!-- Time Worked -->
                                <div style="display: flex !important; align-items: center !important; gap: 6px !important; padding: 4px 10px !important; background-color: #fef3c7 !important; border: 1px solid #fcd34d !important; border-radius: 6px !important;">
                                    <span class="material-symbols-outlined" style="font-size: 14px !important; color: #d97706 !important;">timer</span>
                                    <span style="font-size: 10px !important; color: #92400e !important; font-family: Poppins, sans-serif !important; font-weight: 500 !important;">Time Worked:</span>
                                    <span id="time-worked" style="font-size: 11px !important; color: #b45309 !important; font-family: 'Courier New', monospace !important; font-weight: 600 !important;">00:00:00</span>
                                </div>
                                
                                <!-- Total SLA -->
                                <div style="display: flex !important; align-items: center !important; gap: 6px !important; padding: 4px 10px !important; background-color: #fee2e2 !important; border: 1px solid #fca5a5 !important; border-radius: 6px !important;">
                                    <span class="material-symbols-outlined" style="font-size: 14px !important; color: #dc2626 !important;">hourglass_top</span>
                                    <span style="font-size: 10px !important; color: #991b1b !important; font-family: Poppins, sans-serif !important; font-weight: 500 !important;">Total SLA:</span>
                                    <span id="total-sla" style="font-size: 11px !important; color: #b91c1c !important; font-family: 'Courier New', monospace !important; font-weight: 600 !important;">00:00:00</span>
                                </div>
                            </div>
                        </div>
                        
                        @if($ticket->asset && $ticket->asset->project)
                        <div style="display: flex !important; align-items: center !important; gap: 6px !important; padding: 4px 10px !important; background-color: #f0f9ff !important; border: 1px solid #bae6fd !important; border-radius: 6px !important;">
                            <span class="material-symbols-outlined" style="font-size: 14px !important; color: #0ea5e9 !important;">folder</span>
                            <span style="font-size: 11px !important; color: #0c4a6e !important; font-family: Poppins, sans-serif !important; font-weight: 500 !important;">{{ $ticket->asset->project->name }}</span>
                        </div>
                        @endif
                    </div>
                    <form action="{{ route('helpdesk.reply', $ticket) }}" method="POST" enctype="multipart/form-data" style="padding: 16px !important; background-color: #ffffff !important;">
                        @csrf
                        <div style="display: flex !important; flex-direction: column !important; gap: 12px !important;">
                            <textarea name="message" required rows="3"
                                      style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #111827 !important; resize: none !important;"
                                      placeholder="Type your reply..."></textarea>
                            
                            <div style="display: flex !important; align-items: center !important; justify-content: space-between !important;">
                                <div style="display: flex !important; align-items: center !important; gap: 16px !important;">
                                    <div style="position: relative !important;">
                                        <input type="file" name="attachments[]" multiple id="reply-attachments" style="display: none !important;">
                                        <label for="reply-attachments" 
                                               style="display: inline-flex !important; align-items: center !important; gap: 6px !important; padding: 8px 12px !important; background-color: #f3f4f6 !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-size: 11px !important; font-family: Poppins, sans-serif !important; color: #374151 !important; cursor: pointer !important;">
                                            <span class="material-symbols-outlined" style="font-size: 16px !important;">attach_file</span>
                                            <span id="file-label">Choose Files</span>
                                        </label>
                                    </div>
                                    @if(!$client)
                                    <label style="display: flex !important; align-items: center !important; gap: 8px !important; cursor: pointer !important;">
                                        <input type="checkbox" name="is_internal_note" value="1" 
                                               style="width: 16px !important; height: 16px !important; border-radius: 4px !important; border: 1px solid #d1d5db !important;">
                                        <span style="font-size: 12px !important; color: #4b5563 !important; font-family: Poppins, sans-serif !important;">Internal Note</span>
                                    </label>
                                    @endif
                                </div>
                                <button type="submit" 
                                        style="display: inline-flex !important; align-items: center !important; gap: 6px !important; padding: 10px 20px !important; background-color: #2563eb !important; color: #ffffff !important; border: none !important; border-radius: 6px !important; font-size: 11px !important; font-weight: 500 !important; font-family: Poppins, sans-serif !important; cursor: pointer !important;">
                                    <span class="material-symbols-outlined" style="font-size: 16px !important;">send</span>
                                    SEND REPLY
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                @else
                <div style="border-top: 1px solid #e5e7eb !important; padding: 24px !important; text-align: center !important; background-color: #f9fafb !important;">
                    <span class="material-symbols-outlined" style="font-size: 32px !important; color: #9ca3af !important;">lock</span>
                    <p style="font-size: 12px !important; color: #6b7280 !important; margin: 8px 0 0 0 !important; font-family: Poppins, sans-serif !important;">This ticket is closed.</p>
                </div>
                @endif
            </div>
            <!-- End Chat Container -->

            <!-- Sidebar - Assigned To & Actions -->
            <div style="display: flex !important; flex-direction: column !important; height: 600px !important; border: 1px solid #e5e7eb !important; border-radius: 8px !important; background-color: #ffffff !important; overflow: hidden !important;">
                <!-- Assigned To (Staff only) - Scrollable list -->
                @if(!$client)
                <div style="flex-shrink: 0 !important; border-bottom: 1px solid #e5e7eb !important; max-height: 180px !important; display: flex !important; flex-direction: column !important;">
                    <div style="padding: 12px 16px !important; background-color: #faf5ff !important; border-bottom: 1px solid #e9d5ff !important; display: flex !important; align-items: center !important; gap: 10px !important; flex-shrink: 0 !important;">
                        <div style="width: 32px !important; height: 32px !important; border-radius: 8px !important; background-color: #a855f7 !important; display: flex !important; align-items: center !important; justify-content: center !important;">
                            <span class="material-symbols-outlined" style="font-size: 18px !important; color: #ffffff !important;">group</span>
                        </div>
                        <h4 style="font-size: 12px !important; font-weight: 600 !important; color: #6b21a8 !important; font-family: Poppins, sans-serif !important; margin: 0 !important;">Assigned To</h4>
                    </div>
                    <div style="padding: 12px 16px !important; overflow-y: auto !important; flex: 1 !important;">
                        @if($ticket->assignees->count() > 0)
                        <div style="display: flex !important; flex-direction: column !important; gap: 8px !important;">
                            @foreach($ticket->assignees as $assignee)
                            <div style="display: flex !important; align-items: center !important; gap: 10px !important;">
                                <div style="width: 26px !important; height: 26px !important; border-radius: 50% !important; background-color: #dbeafe !important; display: flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0 !important;">
                                    <span class="material-symbols-outlined" style="font-size: 14px !important; color: #2563eb !important;">person</span>
                                </div>
                                <span style="font-size: 11px !important; color: #374151 !important; font-family: Poppins, sans-serif !important;">{{ $assignee->employee->full_name ?? $assignee->name }}</span>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p style="font-size: 11px !important; color: #9ca3af !important; font-family: Poppins, sans-serif !important; margin: 0 !important;">Not assigned yet</p>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Actions (Staff with assign permission) - NOT scrollable -->
                @if($canAssign)
                <div style="flex: 1 !important; display: flex !important; flex-direction: column !important;">
                    <div style="padding: 12px 16px !important; background-color: #fef3c7 !important; border-bottom: 1px solid #fde68a !important; display: flex !important; align-items: center !important; gap: 10px !important; flex-shrink: 0 !important;">
                        <div style="width: 32px !important; height: 32px !important; border-radius: 8px !important; background-color: #f59e0b !important; display: flex !important; align-items: center !important; justify-content: center !important;">
                            <span class="material-symbols-outlined" style="font-size: 18px !important; color: #ffffff !important;">settings</span>
                        </div>
                        <h4 style="font-size: 12px !important; font-weight: 600 !important; color: #92400e !important; font-family: Poppins, sans-serif !important; margin: 0 !important;">Actions</h4>
                    </div>
                    <div style="padding: 16px !important;">
                        <div style="display: flex !important; flex-direction: column !important; gap: 16px !important;">
                            <!-- Assign Form - Select is scrollable -->
                            <form action="{{ route('helpdesk.assign', $ticket) }}" method="POST">
                                @csrf
                                <label style="display: block !important; font-size: 11px !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important; font-weight: 500 !important;">Assign To</label>
                                <div style="max-height: 180px !important; overflow-y: auto !important; border: 1px solid #d1d5db !important; border-radius: 6px !important;">
                                    <select name="assignee_ids[]" multiple
                                            style="width: 100% !important; padding: 8px 10px !important; border: none !important; font-size: 11px !important; font-family: Poppins, sans-serif !important; min-height: 150px !important;">
                                        @foreach($assignableEmployees as $employee)
                                        <option value="{{ $employee->user_id }}" {{ $ticket->assignees->contains('id', $employee->user_id) ? 'selected' : '' }}>
                                            {{ $employee->full_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <p style="font-size: 10px !important; color: #9ca3af !important; margin: 4px 0 0 0 !important;">Hold Ctrl/Cmd to select multiple</p>
                                <button type="submit" 
                                        style="width: 100% !important; margin-top: 10px !important; padding: 10px 16px !important; background-color: #2563eb !important; color: #ffffff !important; border: none !important; border-radius: 6px !important; font-size: 11px !important; font-weight: 500 !important; font-family: Poppins, sans-serif !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important; gap: 6px !important;">
                                    <span class="material-symbols-outlined" style="font-size: 16px !important;">person_add</span>
                                    UPDATE ASSIGNMENT
                                </button>
                            </form>

                            <!-- Status Update Form -->
                            <form action="{{ route('helpdesk.status', $ticket) }}" method="POST" id="status-form-real">
                                @csrf
                                <label style="display: block !important; font-size: 11px !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important; font-weight: 500 !important;">Update Status</label>
                                <select name="status_id"
                                        style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-size: 11px !important; font-family: Poppins, sans-serif !important;">
                                    @foreach($activeStatuses as $s)
                                    <option value="{{ $s->id }}" {{ $ticket->status_id == $s->id ? 'selected' : '' }}>
                                        {{ $s->name }}
                                    </option>
                                    @endforeach
                                </select>
                                <button type="submit" 
                                        style="width: 100% !important; margin-top: 10px !important; padding: 10px 16px !important; background-color: #059669 !important; color: #ffffff !important; border: none !important; border-radius: 6px !important; font-size: 11px !important; font-weight: 500 !important; font-family: Poppins, sans-serif !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important; gap: 6px !important;">
                                    <span class="material-symbols-outlined" style="font-size: 16px !important;">sync</span>
                                    UPDATE STATUS
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @else
                <!-- For clients or users without assign permission - show ticket info -->
                <div style="flex: 1 !important; display: flex !important; flex-direction: column !important; align-items: center !important; justify-content: center !important; padding: 24px !important;">
                    <span class="material-symbols-outlined" style="font-size: 48px !important; color: #d1d5db !important;">support_agent</span>
                    <p style="font-size: 12px !important; color: #9ca3af !important; margin: 12px 0 0 0 !important; font-family: Poppins, sans-serif !important; text-align: center !important;">Our team is reviewing your ticket</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto-scroll chat to bottom on page load
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chat-messages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Initialize Time Worked and SLA Timer
    initializeTimers();
});

// Time Worked & SLA Timer
function initializeTimers() {
    // Ticket created_at timestamp from server
    const ticketCreatedAt = new Date('{{ $ticket->created_at->toIso8601String() }}');
    const pageLoadedAt = new Date();
    
    // Calculate initial elapsed time from ticket creation to now (Total SLA)
    const initialSlaElapsed = Math.floor((pageLoadedAt - ticketCreatedAt) / 1000);
    
    // Time Worked starts from when user opens this page
    let timeWorkedSeconds = 0;
    let totalSlaSeconds = initialSlaElapsed;
    
    function formatTime(totalSeconds) {
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;
        
        return String(hours).padStart(2, '0') + ':' + 
               String(minutes).padStart(2, '0') + ':' + 
               String(seconds).padStart(2, '0');
    }
    
    function updateTimers() {
        timeWorkedSeconds++;
        totalSlaSeconds++;
        
        const timeWorkedEl = document.getElementById('time-worked');
        const totalSlaEl = document.getElementById('total-sla');
        
        if (timeWorkedEl) {
            timeWorkedEl.textContent = formatTime(timeWorkedSeconds);
        }
        
        if (totalSlaEl) {
            totalSlaEl.textContent = formatTime(totalSlaSeconds);
        }
    }
    
    // Initial display
    const timeWorkedEl = document.getElementById('time-worked');
    const totalSlaEl = document.getElementById('total-sla');
    
    if (timeWorkedEl) {
        timeWorkedEl.textContent = formatTime(timeWorkedSeconds);
    }
    
    if (totalSlaEl) {
        totalSlaEl.textContent = formatTime(totalSlaSeconds);
    }
    
    // Update every second
    setInterval(updateTimers, 1000);
}

// Update file input label when files are selected
document.getElementById('reply-attachments').addEventListener('change', function(e) {
    const fileLabel = document.getElementById('file-label');
    const fileCount = e.target.files.length;
    
    if (fileCount > 0) {
        fileLabel.textContent = fileCount + ' file' + (fileCount > 1 ? 's' : '') + ' selected';
    } else {
        fileLabel.textContent = 'Choose Files';
    }
});
</script>
@endpush

@endsection
