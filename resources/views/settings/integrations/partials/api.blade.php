{{--
    Settings › Integrations › API
    External read-only API consumed by the ATLINE backend.
--}}
@php
    $apiSettings  = $apiSetting->settings ?? [];
    $ipAllowlist  = $apiSettings['ip_allowlist'] ?? '';
    $rateLimit    = $apiSettings['rate_limit'] ?? 120;
    $baseUrl      = rtrim(config('app.url'), '/') . '/api/v1';
    $canUpdate    = auth()->user()->hasPermission('settings_integrations_api.update');
    $canCreate    = auth()->user()->hasPermission('settings_integrations_api.create');
    $canDelete    = auth()->user()->hasPermission('settings_integrations_api.delete');
@endphp

{{-- One-time token reveal --}}
@if(session('new_api_token'))
    <div class="mb-5 border border-amber-300 bg-amber-50 rounded">
        <div class="px-4 py-3 border-b border-amber-200">
            <p class="text-xs font-semibold text-amber-900" style="font-family: Poppins, sans-serif;">
                Token created — copy it now
            </p>
            <p class="text-xs text-amber-700 mt-0.5">
                This is the only time it will be shown. Only a hash is stored, so it cannot be recovered.
            </p>
        </div>
        <div class="px-4 py-3">
            <div class="flex items-center gap-2">
                <input id="newApiToken" type="text" readonly value="{{ session('new_api_token') }}"
                       class="flex-1 px-3 py-2 border border-amber-300 rounded text-xs font-mono bg-white text-gray-900">
                <button type="button" onclick="copyApiToken()"
                        class="px-3 py-2 bg-amber-600 text-white text-xs font-medium rounded hover:bg-amber-700">
                    Copy
                </button>
            </div>
            <p class="text-xs text-amber-700 mt-2">
                For <span class="font-semibold">{{ session('new_api_token_name') }}</span>. Send it as
                <code class="font-mono">Authorization: Bearer &lt;token&gt;</code>.
            </p>
        </div>
    </div>
@endif

{{-- ── Gate settings ────────────────────────────────────────────── --}}
<div class="mb-6">
    <div class="flex items-start justify-between mb-3">
        <div>
            <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">
                External Read-Only API
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">
                Lets approved systems read External Asset Management data. Every endpoint is
                <span class="font-semibold">GET only</span> — nothing here can modify atlinehelp records.
            </p>
        </div>
        <span class="shrink-0 px-2 py-1 text-xs font-medium rounded
            {{ $apiSetting->is_active ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-gray-100 text-gray-500 border border-gray-200' }}">
            {{ $apiSetting->is_active ? 'Enabled' : 'Disabled' }}
        </span>
    </div>

    <form method="POST" action="{{ route('settings.integrations.api.update') }}">
        @csrf
        <fieldset @disabled(!$canUpdate)>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked($apiSetting->is_active)
                           class="rounded border-gray-300 text-blue-600">
                    <span class="text-xs font-medium text-gray-700">Enable the external API</span>
                </label>
                <p class="text-xs text-gray-400 mt-1 ml-6">
                    When off, every endpoint returns 503 regardless of token.
                </p>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-700 mb-1">Base URL</label>
                <div class="flex items-center gap-2">
                    <input id="apiBaseUrl" type="text" readonly value="{{ $baseUrl }}"
                           class="flex-1 px-3 py-2 border border-gray-200 rounded text-xs font-mono bg-gray-50 text-gray-600">
                    <button type="button" onclick="copyBaseUrl()"
                            class="px-3 py-2 border border-gray-300 text-gray-600 text-xs rounded hover:bg-gray-50">
                        Copy
                    </button>
                </div>
                <p class="text-xs text-gray-400 mt-1">
                    Taken from <code class="font-mono">APP_URL</code>. If this looks wrong, fix APP_URL in .env.
                </p>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">IP Allowlist</label>
                <textarea name="ip_allowlist" rows="4" placeholder="203.0.113.10&#10;198.51.100.24"
                          class="w-full px-3 py-2 border border-gray-300 rounded text-xs font-mono focus:ring-1 focus:ring-blue-500 focus:border-blue-500">{{ old('ip_allowlist', $ipAllowlist) }}</textarea>
                <p class="text-xs text-gray-400 mt-1">
                    One per line, or comma separated. Leave blank to allow any IP.
                </p>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Rate limit</label>
                <div class="flex items-center gap-2">
                    <input type="number" name="rate_limit" min="0" max="10000"
                           value="{{ old('rate_limit', $rateLimit) }}"
                           class="w-28 px-3 py-2 border border-gray-300 rounded text-xs focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <span class="text-xs text-gray-500">requests / minute / token</span>
                </div>
                <p class="text-xs text-gray-400 mt-1">0 disables the limit.</p>
            </div>
        </div>

        @if($canUpdate)
        <div class="mt-4">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">
                Save Settings
            </button>
        </div>
        @endif
        </fieldset>
    </form>
</div>

{{-- ── Tokens ───────────────────────────────────────────────────── --}}
<div class="mb-6 border-t border-gray-200 pt-5">
    <div class="flex items-center justify-between mb-3">
        <div>
            <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Access Tokens</h3>
            <p class="text-xs text-gray-500 mt-0.5">Each consuming system should have its own token so it can be revoked alone.</p>
        </div>
    </div>

    @if($canCreate)
    <form method="POST" action="{{ route('settings.integrations.api.tokens.store') }}" class="mb-4">
        @csrf
        <div class="flex flex-wrap items-end gap-2">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Token name</label>
                <input type="text" name="name" required maxlength="100" placeholder="atline-backend"
                       class="px-3 py-2 border border-gray-300 rounded text-xs w-56 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Expires</label>
                <select name="expires_in" class="px-3 py-2 border border-gray-300 rounded text-xs">
                    <option value="never">Never</option>
                    <option value="30">30 days</option>
                    <option value="90">90 days</option>
                    <option value="365">1 year</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-900 text-white text-xs font-medium rounded hover:bg-gray-800">
                Create Token
            </button>
        </div>
    </form>
    @endif

    <div class="border border-gray-200 rounded overflow-x-auto">
        <table class="min-w-full text-xs">
            <thead class="bg-gray-50 text-gray-500">
                <tr>
                    <th class="px-3 py-2 text-left font-medium">Name</th>
                    <th class="px-3 py-2 text-left font-medium">Status</th>
                    <th class="px-3 py-2 text-left font-medium">Last used</th>
                    <th class="px-3 py-2 text-left font-medium">Expires</th>
                    <th class="px-3 py-2 text-left font-medium">Created by</th>
                    <th class="px-3 py-2 text-right font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($apiTokens ?? [] as $t)
                    <tr>
                        <td class="px-3 py-2 text-gray-900 font-medium">{{ $t->name }}</td>
                        <td class="px-3 py-2">
                            @php
                                $badge = match($t->status) {
                                    'active'  => 'bg-green-50 text-green-700 border-green-200',
                                    'revoked' => 'bg-red-50 text-red-700 border-red-200',
                                    default   => 'bg-amber-50 text-amber-700 border-amber-200',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded border {{ $badge }}">{{ ucfirst($t->status) }}</span>
                        </td>
                        <td class="px-3 py-2 text-gray-500">
                            {{ $t->last_used_at ? $t->last_used_at->diffForHumans() : 'Never' }}
                            @if($t->last_used_ip)
                                <span class="text-gray-400 font-mono">· {{ $t->last_used_ip }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-gray-500">
                            {{ $t->expires_at ? $t->expires_at->format('d M Y') : 'Never' }}
                        </td>
                        <td class="px-3 py-2 text-gray-500">{{ $t->creator->name ?? '—' }}</td>
                        <td class="px-3 py-2 text-right whitespace-nowrap">
                            @if($t->revoked_at === null && $canDelete)
                                <form method="POST" action="{{ route('settings.integrations.api.tokens.revoke', $t->id) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            onclick="return confirm('Revoke “{{ $t->name }}”? Any system using it will stop working immediately.')"
                                            class="px-2 py-1 border border-red-200 text-red-600 rounded hover:bg-red-50">
                                        Revoke
                                    </button>
                                </form>
                            @elseif($t->revoked_at !== null && $canDelete)
                                <form method="POST" action="{{ route('settings.integrations.api.tokens.destroy', $t->id) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            onclick="return confirm('Delete this revoked token row permanently?')"
                                            class="px-2 py-1 border border-gray-200 text-gray-500 rounded hover:bg-gray-50">
                                        Delete
                                    </button>
                                </form>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-6 text-center text-gray-400">
                            No tokens yet. Create one so the ATLINE backend can sync.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Endpoint reference ───────────────────────────────────────── --}}
<div class="border-t border-gray-200 pt-5">
    <h3 class="text-sm font-semibold text-gray-900 mb-1" style="font-family: Poppins, sans-serif;">Endpoints</h3>
    <p class="text-xs text-gray-500 mb-3">
        All read-only. Send <code class="font-mono">Authorization: Bearer &lt;token&gt;</code> on every request.
    </p>

    <div class="border border-gray-200 rounded overflow-x-auto">
        <table class="min-w-full text-xs">
            <thead class="bg-gray-50 text-gray-500">
                <tr>
                    <th class="px-3 py-2 text-left font-medium w-16">Method</th>
                    <th class="px-3 py-2 text-left font-medium">Path</th>
                    <th class="px-3 py-2 text-left font-medium">Purpose</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 font-mono">
                @foreach([
                    ['/ping', 'Health check and record counts'],
                    ['/organizations', 'Customers'],
                    ['/organizations/{id}', 'Single customer'],
                    ['/projects', 'Projects — ?organization_id= &updated_since='],
                    ['/projects/{id}', 'Single project'],
                    ['/assets', 'External assets — ?project_id= &status= &updated_since= &page='],
                    ['/assets/{id}', 'Single asset'],
                    ['/assets/lookup?serial=', 'Resolve an asset by serial number'],
                    ['/lookups', 'Categories, brands, locations, vendors'],
                ] as [$path, $purpose])
                    <tr>
                        <td class="px-3 py-2 text-green-700 font-semibold">GET</td>
                        <td class="px-3 py-2 text-gray-900">/api/v1{{ $path }}</td>
                        <td class="px-3 py-2 text-gray-500 font-sans">{{ $purpose }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-3 px-3 py-2 bg-blue-50 border border-blue-200 rounded">
        <p class="text-xs text-blue-800">
            <span class="font-semibold">Incremental sync.</span>
            Pass <code class="font-mono">?updated_since=2026-08-01T00:00:00Z</code> to receive only changed rows.
            Deleted records are returned with a non-null <code class="font-mono">deleted_at</code> so the consumer
            can remove its mirrored copy.
        </p>
    </div>
</div>

<script>
function copyApiToken() {
    const el = document.getElementById('newApiToken');
    if (!el) return;
    el.select();
    navigator.clipboard.writeText(el.value).catch(() => document.execCommand('copy'));
}
function copyBaseUrl() {
    const el = document.getElementById('apiBaseUrl');
    if (!el) return;
    el.select();
    navigator.clipboard.writeText(el.value).catch(() => document.execCommand('copy'));
}
</script>
