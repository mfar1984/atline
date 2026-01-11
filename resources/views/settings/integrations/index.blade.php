@extends('layouts.app')

@section('title', 'Integrations')

@section('page-title', 'Integrations')

@section('content')
<div class="bg-white border border-gray-200">
    <!-- Page Header -->
    <div class="px-6 py-4 flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Integrations</h2>
            <p class="text-xs text-gray-500 mt-0.5">Manage third-party integrations and API connections</p>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="border-t border-gray-200">
        <nav class="flex px-6" aria-label="Tabs">
            @permission('settings_integrations_recycle_bin.view')
            <a href="{{ route('settings.integrations.index', ['tab' => 'recycle-bin']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'recycle-bin' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Recycle Bin
            </a>
            @endpermission
            @permission('settings_integrations_email.view')
            <a href="{{ route('settings.integrations.index', ['tab' => 'email']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'email' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Email
            </a>
            @endpermission
            @permission('settings_integrations_telegram.view')
            <a href="{{ route('settings.integrations.index', ['tab' => 'telegram']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'telegram' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Telegram
            </a>
            @endpermission
            @permission('settings_integrations_payment.view')
            <a href="{{ route('settings.integrations.index', ['tab' => 'payment']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'payment' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Payment Gateway
            </a>
            @endpermission
            @permission('settings_integrations_storage.view')
            <a href="{{ route('settings.integrations.index', ['tab' => 'storage']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'storage' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Storage
            </a>
            @endpermission
            @permission('settings_integrations_weather.view')
            <a href="{{ route('settings.integrations.index', ['tab' => 'weather']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'weather' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Weather
            </a>
            @endpermission
            @permission('settings_integrations_webhook.view')
            <a href="{{ route('settings.integrations.index', ['tab' => 'webhooks']) }}"
               class="px-4 py-3 text-xs font-medium border-b-2 {{ $activeTab === 'webhooks' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
               style="font-family: Poppins, sans-serif;">
                Webhooks
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

        @if($activeTab === 'recycle-bin')
            @include('settings.integrations.partials.recycle-bin')
        @elseif($activeTab === 'email')
            @include('settings.integrations.partials.email')
        @elseif($activeTab === 'telegram')
            @include('settings.integrations.partials.telegram')
        @elseif($activeTab === 'payment')
            @include('settings.integrations.partials.payment')
        @elseif($activeTab === 'storage')
            @include('settings.integrations.partials.storage')
        @elseif($activeTab === 'weather')
            @include('settings.integrations.partials.weather')
        @elseif($activeTab === 'webhooks')
            @include('settings.integrations.partials.webhooks')
        @endif
    </div>
</div>
@endsection
