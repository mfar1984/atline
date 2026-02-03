@extends('layouts.app')

@section('title', 'Activity Logs')

@section('page-title', 'Activity Logs')

@section('content')
<div class="bg-white border border-gray-200">
    <!-- Page Header -->
    <div class="px-6 py-4 flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Activity Logs</h2>
            <p class="text-xs text-gray-500 mt-0.5">Monitor system activities and audit trails</p>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="border-t border-gray-200">
        <nav class="flex px-6" aria-label="Tabs">
            <a href="{{ route('settings.activity-logs.index', ['tab' => 'activity']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'activity' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Activity Log
            </a>
            <a href="{{ route('settings.activity-logs.index', ['tab' => 'audit']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'audit' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Audit Log
            </a>
            <a href="{{ route('settings.activity-logs.index', ['tab' => 'suspicious']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'suspicious' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                <span class="inline-flex items-center gap-1">
                    Suspicious Log
                    @if(isset($suspiciousStats) && $suspiciousStats['total'] > 0)
                        <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-600" style="font-size: 9px; min-width: 18px;">
                            {{ $suspiciousStats['total'] }}
                        </span>
                    @endif
                </span>
            </a>
            <a href="{{ route('settings.activity-logs.index', ['tab' => 'banned']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'banned' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                <span class="inline-flex items-center gap-1">
                    User Banned
                    @if(isset($bannedCount) && $bannedCount > 0)
                        <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-600" style="font-size: 9px; min-width: 18px;">
                            {{ $bannedCount }}
                        </span>
                    @endif
                </span>
            </a>
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

        @if($activeTab === 'activity')
            @include('settings.activity-logs.partials.activity')
        @elseif($activeTab === 'audit')
            @include('settings.activity-logs.partials.audit')
        @elseif($activeTab === 'suspicious')
            @include('settings.activity-logs.partials.suspicious')
        @elseif($activeTab === 'banned')
            @include('settings.activity-logs.partials.banned')
        @endif
    </div>
</div>
@endsection
