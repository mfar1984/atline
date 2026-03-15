<div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Suspicious Activity Detection</h3>
            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded bg-yellow-100 text-yellow-700">
                <span class="material-symbols-outlined" style="font-size: 12px;">schedule</span>
                Last 7 Days
            </span>
        </div>
    </div>

    @if($suspiciousStats ?? false)
    <!-- Stats Overview - 7 cards in 1 row -->
    <div class="grid grid-cols-7 gap-2 mb-4">
        <div class="p-3 bg-white border border-gray-200 rounded text-center">
            <p class="text-xl font-bold {{ $suspiciousStats['total'] > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $suspiciousStats['total'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Total</p>
        </div>
        <div class="p-3 bg-white border border-gray-200 rounded text-center">
            <p class="text-xl font-bold {{ $suspiciousStats['failed_logins'] > 0 ? 'text-orange-600' : 'text-gray-400' }}">{{ $suspiciousStats['failed_logins'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Failed Logins</p>
        </div>
        <div class="p-3 bg-white border border-gray-200 rounded text-center">
            <p class="text-xl font-bold {{ $suspiciousStats['brute_force_attempts'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $suspiciousStats['brute_force_attempts'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Brute Force</p>
        </div>
        <div class="p-3 bg-white border border-gray-200 rounded text-center">
            <p class="text-xl font-bold {{ $suspiciousStats['injection_attempts'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $suspiciousStats['injection_attempts'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Injection</p>
        </div>
        <div class="p-3 bg-white border border-gray-200 rounded text-center">
            <p class="text-xl font-bold {{ $suspiciousStats['unusual_hours'] > 0 ? 'text-yellow-600' : 'text-gray-400' }}">{{ $suspiciousStats['unusual_hours'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Unusual Hours</p>
        </div>
        <div class="p-3 bg-white border border-gray-200 rounded text-center">
            <p class="text-xl font-bold {{ $suspiciousStats['mass_operations'] > 0 ? 'text-orange-600' : 'text-gray-400' }}">{{ $suspiciousStats['mass_operations'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Mass Ops</p>
        </div>
        <div class="p-3 bg-white border border-gray-200 rounded text-center">
            <p class="text-xl font-bold {{ $suspiciousStats['multiple_ips'] > 0 ? 'text-yellow-600' : 'text-gray-400' }}">{{ $suspiciousStats['multiple_ips'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Multiple IPs</p>
        </div>
    </div>

    <!-- Legend -->
    <div class="mb-4 p-3 bg-gray-50 border border-gray-200 rounded">
        <p class="text-xs font-medium text-gray-700 mb-2">Risk Level:</p>
        <div class="flex flex-wrap gap-4">
            <span class="inline-flex items-center gap-1 text-xs">
                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                High - Injection/Attack
            </span>
            <span class="inline-flex items-center gap-1 text-xs">
                <span class="w-3 h-3 rounded-full bg-orange-500"></span>
                Medium - Failed logins, Multiple IPs
            </span>
            <span class="inline-flex items-center gap-1 text-xs">
                <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                Low - Unusual hours
            </span>
        </div>
    </div>
    @endif

    @if($suspiciousStats && $suspiciousStats['total'] === 0)
        <!-- No Suspicious Activity -->
        <div class="border border-green-200 rounded bg-green-50 p-8 text-center">
            <span class="material-symbols-outlined text-green-400" style="font-size: 64px;">verified_user</span>
            <h4 class="mt-4 text-sm font-medium text-green-700" style="font-family: Poppins, sans-serif;">No Suspicious Activity Detected</h4>
            <p class="mt-2 text-xs text-green-600" style="font-family: Poppins, sans-serif;">
                All activity in the last 7 days appears normal. No failed logins, injection attempts, or unusual patterns detected.
            </p>
        </div>
    @else
        <!-- Suspicious Activity Table -->
        <div class="overflow-x-auto border border-gray-200 rounded">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 80px;">Risk</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 140px;">Date/Time</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 120px;">User</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 180px;">Suspicious Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 100px;">Action</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Description</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 120px;">IP Address</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($suspiciousLogs ?? [] as $log)
                    @php
                        $riskColors = [
                            'high' => 'bg-red-50 border-l-4 border-l-red-500',
                            'medium' => 'bg-orange-50 border-l-4 border-l-orange-500',
                            'low' => 'bg-yellow-50 border-l-4 border-l-yellow-500',
                        ];
                        $riskBadgeColors = [
                            'high' => 'bg-red-100 text-red-700',
                            'medium' => 'bg-orange-100 text-orange-700',
                            'low' => 'bg-yellow-100 text-yellow-700',
                        ];
                    @endphp
                    <tr class="{{ $riskColors[$log->risk_level] ?? '' }}">
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded {{ $riskBadgeColors[$log->risk_level] ?? 'bg-gray-100 text-gray-700' }}" style="font-size: 10px;">
                                {{ ucfirst($log->risk_level) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap" style="font-family: Poppins, sans-serif;">
                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-900" style="font-family: Poppins, sans-serif;">
                            {{ $log->user?->name ?? 'Unknown' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-700" style="font-size: 10px;">
                                @if($log->suspicious_type === 'Possible Injection Attempt')
                                    <span class="material-symbols-outlined text-red-500" style="font-size: 12px;">bug_report</span>
                                @elseif($log->suspicious_type === 'Failed Login')
                                    <span class="material-symbols-outlined text-orange-500" style="font-size: 12px;">lock</span>
                                @elseif($log->suspicious_type === 'Unusual Hours Activity')
                                    <span class="material-symbols-outlined text-yellow-500" style="font-size: 12px;">schedule</span>
                                @elseif($log->suspicious_type === 'Multiple IPs Detected')
                                    <span class="material-symbols-outlined text-orange-500" style="font-size: 12px;">lan</span>
                                @else
                                    <span class="material-symbols-outlined text-gray-500" style="font-size: 12px;">warning</span>
                                @endif
                                {{ $log->suspicious_type }}
                            </span>
                            @if($log->detected_pattern ?? false)
                                <p class="text-xs text-red-600 mt-1">Pattern: {{ $log->detected_pattern }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded {{ $log->action_color }}" style="font-size: 10px;">
                                {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600" style="font-family: Poppins, sans-serif;">
                            <span class="truncate block" title="{{ $log->description }}">{{ \Illuminate\Support\Str::limit($log->description, 60) }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500" style="font-family: Poppins, sans-serif;">
                            {{ $log->ip_address ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500 text-xs">
                            <span class="material-symbols-outlined text-gray-300" style="font-size: 48px;">shield</span>
                            <p class="mt-2">No suspicious activity detected.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(count($suspiciousLogs ?? []) >= 100)
        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
            <p class="text-xs text-yellow-700">
                <span class="material-symbols-outlined align-middle" style="font-size: 14px;">info</span>
                Showing first 100 suspicious activities. There may be more entries not displayed.
            </p>
        </div>
        @endif
    @endif

    <!-- Detection Info -->
    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded">
        <p class="text-xs font-medium text-blue-700 mb-2">Detection Rules:</p>
        <div class="grid grid-cols-2 gap-2 text-xs text-blue-600">
            <div>• <strong>Failed Logins</strong> - All failed login attempts</div>
            <div>• <strong>Brute Force</strong> - 3+ failed logins from same IP</div>
            <div>• <strong>Injection</strong> - SQL/XSS patterns in inputs</div>
            <div>• <strong>Unusual Hours</strong> - Before 6 AM or after 10 PM</div>
            <div>• <strong>Mass Operations</strong> - 20+ same action per day</div>
            <div>• <strong>Multiple IPs</strong> - 3+ IPs per user in 24 hours</div>
        </div>
    </div>
</div>
