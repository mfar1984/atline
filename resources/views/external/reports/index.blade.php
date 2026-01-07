@extends('layouts.app')

@section('title', 'Reports & Analytics')

@section('page-title', 'Reports')

@section('content')
<div style="background-color: #ffffff; border: 1px solid #e5e7eb;">
    <!-- Header -->
    <div style="padding: 16px 24px; border-bottom: 1px solid #e5e7eb;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h2 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0; font-family: Poppins, sans-serif;">Reports & Analytics</h2>
                <p style="font-size: 12px; color: #6b7280; margin-top: 4px;">Overview of projects, assets and performance metrics</p>
            </div>
            <button type="button" onclick="window.print()" style="display: inline-flex; align-items: center; gap: 8px; padding: 0 12px; min-height: 32px; background-color: #3b82f6; color: white; font-size: 11px; font-weight: 500; border-radius: 4px; border: none; cursor: pointer;">
                <span class="material-symbols-outlined" style="font-size: 14px;">print</span>
                PRINT
            </button>
        </div>
    </div>

    <div style="padding: 24px;">
        <!-- Summary Stats Cards -->
        @if($isClient ?? false)
        <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-bottom: 24px;">
        @else
        <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 16px; margin-bottom: 24px;">
        @endif
            <div style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border-radius: 8px; padding: 16px; color: white;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                    <div>
                        <p style="font-size: 11px; opacity: 0.8; margin: 0;">Total Projects</p>
                        <p style="font-size: 24px; font-weight: 700; margin: 4px 0 0 0;">{{ $stats['total_projects'] }}</p>
                    </div>
                    <span class="material-symbols-outlined" style="font-size: 32px; opacity: 0.5;">folder</span>
                </div>
            </div>
            <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 8px; padding: 16px; color: white;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                    <div>
                        <p style="font-size: 11px; opacity: 0.8; margin: 0;">Active</p>
                        <p style="font-size: 24px; font-weight: 700; margin: 4px 0 0 0;">{{ $stats['active_projects'] }}</p>
                    </div>
                    <span class="material-symbols-outlined" style="font-size: 32px; opacity: 0.5;">play_circle</span>
                </div>
            </div>
            <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border-radius: 8px; padding: 16px; color: white;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                    <div>
                        <p style="font-size: 11px; opacity: 0.8; margin: 0;">Completed</p>
                        <p style="font-size: 24px; font-weight: 700; margin: 4px 0 0 0;">{{ $stats['completed_projects'] }}</p>
                    </div>
                    <span class="material-symbols-outlined" style="font-size: 32px; opacity: 0.5;">check_circle</span>
                </div>
            </div>
            <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 8px; padding: 16px; color: white;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                    <div>
                        <p style="font-size: 11px; opacity: 0.8; margin: 0;">Total Assets</p>
                        <p style="font-size: 24px; font-weight: 700; margin: 4px 0 0 0;">{{ $stats['total_assets'] }}</p>
                    </div>
                    <span class="material-symbols-outlined" style="font-size: 32px; opacity: 0.5;">inventory_2</span>
                </div>
            </div>
            @if(!($isClient ?? false))
            <div style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 8px; padding: 16px; color: white;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                    <div>
                        <p style="font-size: 11px; opacity: 0.8; margin: 0;">Total Value</p>
                        <p style="font-size: 18px; font-weight: 700; margin: 4px 0 0 0;">RM {{ number_format($stats['total_value'], 0) }}</p>
                    </div>
                    <span class="material-symbols-outlined" style="font-size: 32px; opacity: 0.5;">payments</span>
                </div>
            </div>
            @endif
            <div style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); border-radius: 8px; padding: 16px; color: white;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                    <div>
                        <p style="font-size: 11px; opacity: 0.8; margin: 0;">Clients</p>
                        <p style="font-size: 24px; font-weight: 700; margin: 4px 0 0 0;">{{ $stats['total_clients'] }}</p>
                    </div>
                    <span class="material-symbols-outlined" style="font-size: 32px; opacity: 0.5;">groups</span>
                </div>
            </div>
        </div>

        <!-- Row 1: Project Status Chart + Top Projects Table -->
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 16px; margin-bottom: 24px;">
            <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; border: 1px solid #f3f4f6;">
                <h3 style="font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 16px 0;">Project Status</h3>
                <div style="height: 200px;"><canvas id="projectStatusChart"></canvas></div>
            </div>
            <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; border: 1px solid #f3f4f6;">
                <h3 style="font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 16px 0;">Top Projects by Value</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <th style="text-align: left; padding: 8px 0; font-size: 11px; font-weight: 600; color: #6b7280;">Project</th>
                            <th style="text-align: left; padding: 8px 0; font-size: 11px; font-weight: 600; color: #6b7280;">Client</th>
                            @if(!($isClient ?? false))
                            <th style="text-align: right; padding: 8px 0; font-size: 11px; font-weight: 600; color: #6b7280;">Value (RM)</th>
                            @endif
                            <th style="text-align: center; padding: 8px 0; font-size: 11px; font-weight: 600; color: #6b7280;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topProjects as $project)
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 10px 0;"><span style="font-size: 11px; font-weight: 500; color: #111827;">{{ $project->name }}</span></td>
                            <td style="padding: 10px 0;"><span style="font-size: 11px; color: #6b7280;">{{ $project->client?->name ?? '-' }}</span></td>
                            @if(!($isClient ?? false))
                            <td style="padding: 10px 0; text-align: right;"><span style="font-size: 11px; font-weight: 600; color: #111827;">{{ number_format($project->project_value, 2) }}</span></td>
                            @endif
                            <td style="padding: 10px 0; text-align: center;">
                                @php
                                    $statusColors = ['active' => '#10b981', 'completed' => '#3b82f6', 'on_hold' => '#f59e0b'];
                                @endphp
                                <span style="display: inline-block; padding: 2px 8px; font-size: 10px; font-weight: 500; border-radius: 9999px; background-color: {{ $statusColors[$project->status] ?? '#6b7280' }}20; color: {{ $statusColors[$project->status] ?? '#6b7280' }};">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Row 2: Assets by Category + Top Brands -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
            <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; border: 1px solid #f3f4f6;">
                <h3 style="font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 16px 0;">Assets by Category</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div style="height: 180px;"><canvas id="assetCategoryChart"></canvas></div>
                    <div style="max-height: 180px; overflow-y: auto;">
                        @php $categoryColors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16']; @endphp
                        @foreach($assetsByCategory as $cat)
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f3f4f6;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 10px; height: 10px; border-radius: 2px; background-color: {{ $categoryColors[$loop->index % count($categoryColors)] }};"></div>
                                <span style="font-size: 11px; color: #4b5563;">{{ $cat['name'] }}</span>
                            </div>
                            <span style="font-size: 11px; font-weight: 600; color: #111827;">{{ $cat['count'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; border: 1px solid #f3f4f6;">
                <h3 style="font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 16px 0;">Top Brands</h3>
                <div style="height: 200px;"><canvas id="brandChart"></canvas></div>
            </div>
        </div>

        <!-- Row 3: Project Value by Client + Top Vendors -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
            <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; border: 1px solid #f3f4f6;">
                <h3 style="font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 16px 0;">Project Value by Client</h3>
                <div style="height: 200px;"><canvas id="clientValueChart"></canvas></div>
            </div>
            <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; border: 1px solid #f3f4f6;">
                <h3 style="font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 16px 0;">Top Vendors</h3>
                @php $maxVendor = $topVendors->max('count') ?: 1; @endphp
                @foreach($topVendors as $vendor)
                @php $pct = ($vendor['count'] / $maxVendor) * 100; @endphp
                <div style="margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span style="font-size: 11px; color: #4b5563;">{{ $vendor['name'] }}</span>
                        <span style="font-size: 11px; font-weight: 600; color: #111827;">{{ $vendor['count'] }} assets</span>
                    </div>
                    <div style="height: 8px; background-color: #e5e7eb; border-radius: 4px; overflow: hidden;">
                        <div style="height: 100%; width: {{ $pct }}%; background: linear-gradient(90deg, #3b82f6, #8b5cf6); border-radius: 4px;"></div>
                    </div>
                </div>
                @endforeach
                @if($topVendors->isEmpty())
                <p style="font-size: 11px; color: #9ca3af; text-align: center; padding: 40px 0;">No vendor data available</p>
                @endif
            </div>
        </div>

        <!-- Row 4: Asset Value Distribution + Recent Assets -->
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 16px;">
            <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; border: 1px solid #f3f4f6;">
                <h3 style="font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 16px 0;">Asset Value by Category</h3>
                <div style="height: 220px;"><canvas id="categoryValueChart"></canvas></div>
            </div>
            <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; border: 1px solid #f3f4f6;">
                <h3 style="font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 16px 0;">Recent Assets</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <th style="text-align: left; padding: 8px 0; font-size: 11px; font-weight: 600; color: #6b7280;">Asset Tag</th>
                            <th style="text-align: left; padding: 8px 0; font-size: 11px; font-weight: 600; color: #6b7280;">Category</th>
                            <th style="text-align: left; padding: 8px 0; font-size: 11px; font-weight: 600; color: #6b7280;">Brand</th>
                            @if(!($isClient ?? false))
                            <th style="text-align: right; padding: 8px 0; font-size: 11px; font-weight: 600; color: #6b7280;">Price (RM)</th>
                            @endif
                            <th style="text-align: center; padding: 8px 0; font-size: 11px; font-weight: 600; color: #6b7280;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentAssets as $asset)
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 10px 0;"><span style="font-size: 11px; font-weight: 500; color: #111827;">{{ $asset->asset_tag }}</span></td>
                            <td style="padding: 10px 0;"><span style="font-size: 11px; color: #6b7280;">{{ $asset->category?->name ?? '-' }}</span></td>
                            <td style="padding: 10px 0;"><span style="font-size: 11px; color: #111827;">{{ $asset->brand?->name ?? '-' }}</span></td>
                            @if(!($isClient ?? false))
                            <td style="padding: 10px 0; text-align: right;"><span style="font-size: 11px; font-weight: 600; color: #111827;">@formatCurrency($asset->unit_price, false)</span></td>
                            @endif
                            <td style="padding: 10px 0; text-align: center;"><span style="font-size: 10px; color: #9ca3af;">@formatDate($asset->created_at)</span></td>
                        </tr>
                        @endforeach
                        @if($recentAssets->isEmpty())
                        <tr>
                            <td colspan="{{ ($isClient ?? false) ? '4' : '5' }}" style="padding: 40px 0; text-align: center;"><span style="font-size: 11px; color: #9ca3af;">No assets found</span></td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Project Status Doughnut Chart
    new Chart(document.getElementById('projectStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Completed', 'On Hold'],
            datasets: [{
                data: [
                    {{ $projectsByStatus['active'] ?? 0 }},
                    {{ $projectsByStatus['completed'] ?? 0 }},
                    {{ $projectsByStatus['on_hold'] ?? 0 }}
                ],
                backgroundColor: ['#10b981', '#3b82f6', '#f59e0b'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 10 }, padding: 12 } }
            },
            cutout: '60%'
        }
    });

    // Assets by Category Doughnut Chart
    new Chart(document.getElementById('assetCategoryChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($assetsByCategory->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode($assetsByCategory->pluck('count')) !!},
                backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            cutout: '55%'
        }
    });

    // Top Brands Horizontal Bar Chart
    new Chart(document.getElementById('brandChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($assetsByBrand->pluck('name')) !!},
            datasets: [{
                label: 'Assets',
                data: {!! json_encode($assetsByBrand->pluck('count')) !!},
                backgroundColor: '#3b82f6',
                borderRadius: 4,
                barThickness: 16
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: { grid: { display: false }, ticks: { font: { size: 10 } } }
            }
        }
    });

    // Project Value by Client Horizontal Bar
    new Chart(document.getElementById('clientValueChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($projectValueByClient->pluck('name')) !!},
            datasets: [{
                label: 'Value (RM)',
                data: {!! json_encode($projectValueByClient->pluck('value')) !!},
                backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                borderRadius: 4,
                barThickness: 20
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 }, callback: function(v) { return 'RM ' + v.toLocaleString(); } } },
                y: { grid: { display: false }, ticks: { font: { size: 10 } } }
            }
        }
    });

    // Asset Value by Category Polar Area Chart
    new Chart(document.getElementById('categoryValueChart'), {
        type: 'polarArea',
        data: {
            labels: {!! json_encode($assetValueByCategory->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode($assetValueByCategory->pluck('value')) !!},
                backgroundColor: ['#3b82f680', '#10b98180', '#f59e0b80', '#ef444480', '#8b5cf680', '#ec489980', '#06b6d480', '#84cc1680']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { font: { size: 9 }, padding: 8 } }
            }
        }
    });
});
</script>
@endpush
@endsection
