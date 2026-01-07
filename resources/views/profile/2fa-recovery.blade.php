@extends('layouts.app')

@section('title', 'Recovery Codes')

@section('page-title', 'Recovery Codes')

@section('content')
<div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
    @if(session('success'))
    <div class="mx-6 mt-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded" style="font-size: 11px;">
        {{ session('success') }}
    </div>
    @endif

    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h2 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Recovery Codes</h2>
            <p class="text-gray-500 mt-0.5" style="font-size: 11px;">Use these codes if you lose access to your authenticator app</p>
        </div>
        <a href="{{ route('profile.index') }}" class="inline-flex items-center gap-2 px-3 bg-gray-500 text-white text-xs font-medium rounded hover:bg-gray-600 transition" style="min-height: 32px; font-size: 11px;">
            <span class="material-symbols-outlined" style="font-size: 14px;">arrow_back</span>
            BACK
        </a>
    </div>

    <div class="p-6">
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
            <div class="flex gap-3">
                <span class="material-symbols-outlined text-amber-600" style="font-size: 18px;">warning</span>
                <div>
                    <p class="text-amber-800" style="font-size: 11px; font-weight: 500;">Keep these codes safe!</p>
                    <p class="text-amber-700 mt-1" style="font-size: 11px;">Each code can only be used once. Store them in a secure location like a password manager.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            @foreach($recoveryCodes as $code)
            <div class="p-3 bg-gray-100 rounded font-mono tracking-wider text-center select-all" style="font-size: 11px;">
                {{ $code }}
            </div>
            @endforeach
        </div>

        <div class="mt-6 flex items-center justify-between">
            <form action="{{ route('profile.2fa.regenerate') }}" method="POST" onsubmit="return confirm('Are you sure? This will invalidate all existing recovery codes.')">
                @csrf
                <button type="submit" class="inline-flex items-center px-3 bg-gray-100 text-gray-700 text-xs font-medium rounded hover:bg-gray-200 transition" style="min-height: 32px; font-size: 11px;">
                    <span class="material-symbols-outlined mr-1" style="font-size: 14px;">refresh</span>
                    REGENERATE CODES
                </button>
            </form>

            <button type="button" onclick="copyRecoveryCodes()" class="inline-flex items-center px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px; font-size: 11px;">
                <span class="material-symbols-outlined mr-1" style="font-size: 14px;">content_copy</span>
                COPY ALL CODES
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyRecoveryCodes() {
    const codes = @json($recoveryCodes);
    const text = codes.join('\n');
    navigator.clipboard.writeText(text).then(() => {
        alert('Recovery codes copied to clipboard!');
    });
}
</script>
@endpush
@endsection
