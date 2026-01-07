@extends('layouts.app')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard')

@section('content')
<div style="background-color: #ffffff; border: 1px solid #e5e7eb;">
    <!-- Header -->
    <div style="padding: 16px 24px; border-bottom: 1px solid #e5e7eb;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h2 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0; font-family: Poppins, sans-serif;">Dashboard</h2>
                <p style="font-size: 12px; color: #6b7280; margin-top: 4px;">Welcome back! Here's what's happening.</p>
            </div>
            <div style="font-size: 11px; color: #6b7280;">
                <span class="material-symbols-outlined" style="font-size: 14px; vertical-align: middle;">schedule</span>
                @formatDateTime(now())
            </div>
        </div>
    </div>

    <div style="padding: 24px;">
        <!-- Quick Stats Row - 4 Key Metrics Only -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
            <!-- Projects -->
            <div style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border-radius: 12px; padding: 20px; color: white;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="font-size: 12px; opacity: 0.8; margin: 0;">Projects</p>
                        <p style="font-size: 32px; font-weight: 700; margin: 8px 0 4px 0;">{{ $externalStats['total_projects'] }}</p>
                        <p style="font-size: 11px; opacity: 0.7; margin: 0;">{{ $externalStats['active_projects'] }} active</p>
                    </div>
                    <span class="material-symbols-outlined" style="font-size: 48px; opacity: 0.3;">folder</span>
                </div>
            </div>

            <!-- Assets -->
            <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; padding: 20px; color: white;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="font-size: 12px; opacity: 0.8; margin: 0;">Total Assets</p>
                        <p style="font-size: 32px; font-weight: 700; margin: 8px 0 4px 0;">{{ $externalStats['total_assets'] + $internalStats['total_internal_assets'] }}</p>
                        <p style="font-size: 11px; opacity: 0.7; margin: 0;">{{ $externalStats['total_assets'] }} external, {{ $internalStats['total_internal_assets'] }} internal</p>
                    </div>
                    <span class="material-symbols-outlined" style="font-size: 48px; opacity: 0.3;">inventory_2</span>
                </div>
            </div>

            <!-- Employees -->
            <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border-radius: 12px; padding: 20px; color: white;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="font-size: 12px; opacity: 0.8; margin: 0;">Employees</p>
                        <p style="font-size: 32px; font-weight: 700; margin: 8px 0 4px 0;">{{ $internalStats['total_employees'] }}</p>
                        <p style="font-size: 11px; opacity: 0.7; margin: 0;">{{ $internalStats['active_employees'] }} active</p>
                    </div>
                    <span class="material-symbols-outlined" style="font-size: 48px; opacity: 0.3;">groups</span>
                </div>
            </div>

            <!-- Clients & Vendors -->
            <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; padding: 20px; color: white;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="font-size: 12px; opacity: 0.8; margin: 0;">Partners</p>
                        <p style="font-size: 32px; font-weight: 700; margin: 8px 0 4px 0;">{{ $externalStats['total_clients'] + $externalStats['total_vendors'] }}</p>
                        <p style="font-size: 11px; opacity: 0.7; margin: 0;">{{ $externalStats['total_clients'] }} clients, {{ $externalStats['total_vendors'] }} vendors</p>
                    </div>
                    <span class="material-symbols-outlined" style="font-size: 48px; opacity: 0.3;">handshake</span>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 24px;">
            <!-- Left Column - Recent Projects -->
            <div style="background-color: #f9fafb; border-radius: 12px; padding: 20px; border: 1px solid #f3f4f6;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <h3 style="font-size: 14px; font-weight: 600; color: #111827; margin: 0;">Recent Projects</h3>
                    <a href="{{ route('external.projects.index') }}" style="font-size: 11px; color: #3b82f6; text-decoration: none; display: flex; align-items: center; gap: 4px;">
                        View All <span class="material-symbols-outlined" style="font-size: 14px;">arrow_forward</span>
                    </a>
                </div>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <th style="text-align: left; padding: 10px 0; font-size: 11px; font-weight: 600; color: #6b7280;">Project</th>
                            <th style="text-align: left; padding: 10px 0; font-size: 11px; font-weight: 600; color: #6b7280;">Client</th>
                            <th style="text-align: center; padding: 10px 0; font-size: 11px; font-weight: 600; color: #6b7280;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentProjects as $project)
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 12px 0;">
                                <a href="{{ route('external.projects.show', $project) }}" style="font-size: 12px; font-weight: 500; color: #111827; text-decoration: none;">{{ Str::limit($project->name, 35) }}</a>
                            </td>
                            <td style="padding: 12px 0;"><span style="font-size: 12px; color: #6b7280;">{{ $project->client?->name ?? '-' }}</span></td>
                            <td style="padding: 12px 0; text-align: center;">
                                @php
                                    $statusColors = ['active' => '#10b981', 'completed' => '#3b82f6', 'on_hold' => '#f59e0b'];
                                @endphp
                                <span style="display: inline-block; padding: 4px 10px; font-size: 10px; font-weight: 500; border-radius: 9999px; background-color: {{ $statusColors[$project->status] ?? '#6b7280' }}15; color: {{ $statusColors[$project->status] ?? '#6b7280' }};">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="padding: 40px 0; text-align: center;"><span style="font-size: 12px; color: #9ca3af;">No projects found</span></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Right Column - Project Status Chart -->
            <div style="background-color: #f9fafb; border-radius: 12px; padding: 20px; border: 1px solid #f3f4f6;">
                <h3 style="font-size: 14px; font-weight: 600; color: #111827; margin: 0 0 16px 0;">Project Status</h3>
                <div style="height: 220px;"><canvas id="projectStatusChart"></canvas></div>
            </div>
        </div>

        <!-- Second Row -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <!-- Recent Employees -->
            <div style="background-color: #f9fafb; border-radius: 12px; padding: 20px; border: 1px solid #f3f4f6;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <h3 style="font-size: 14px; font-weight: 600; color: #111827; margin: 0;">Recent Employees</h3>
                    <a href="{{ route('internal.employee.index') }}" style="font-size: 11px; color: #3b82f6; text-decoration: none; display: flex; align-items: center; gap: 4px;">
                        View All <span class="material-symbols-outlined" style="font-size: 14px;">arrow_forward</span>
                    </a>
                </div>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <th style="text-align: left; padding: 10px 0; font-size: 11px; font-weight: 600; color: #6b7280;">Name</th>
                            <th style="text-align: left; padding: 10px 0; font-size: 11px; font-weight: 600; color: #6b7280;">Position</th>
                            <th style="text-align: center; padding: 10px 0; font-size: 11px; font-weight: 600; color: #6b7280;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentEmployees as $employee)
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 12px 0;">
                                <a href="{{ route('internal.employee.show', $employee) }}" style="font-size: 12px; font-weight: 500; color: #111827; text-decoration: none;">{{ $employee->full_name }}</a>
                            </td>
                            <td style="padding: 12px 0;"><span style="font-size: 12px; color: #6b7280;">{{ $employee->position ?? '-' }}</span></td>
                            <td style="padding: 12px 0; text-align: center;">
                                @php
                                    $empStatusColors = ['active' => '#10b981', 'inactive' => '#6b7280', 'on_leave' => '#f59e0b', 'terminated' => '#ef4444'];
                                @endphp
                                <span style="display: inline-block; padding: 4px 10px; font-size: 10px; font-weight: 500; border-radius: 9999px; background-color: {{ $empStatusColors[$employee->status] ?? '#6b7280' }}15; color: {{ $empStatusColors[$employee->status] ?? '#6b7280' }};">{{ ucfirst(str_replace('_', ' ', $employee->status)) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="padding: 40px 0; text-align: center;"><span style="font-size: 12px; color: #9ca3af;">No employees found</span></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Asset Movements / Alerts -->
            <div style="background-color: #f9fafb; border-radius: 12px; padding: 20px; border: 1px solid #f3f4f6;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <h3 style="font-size: 14px; font-weight: 600; color: #111827; margin: 0;">Asset Checkouts</h3>
                    <a href="{{ route('internal.inventory.index') }}" style="font-size: 11px; color: #3b82f6; text-decoration: none; display: flex; align-items: center; gap: 4px;">
                        View All <span class="material-symbols-outlined" style="font-size: 14px;">arrow_forward</span>
                    </a>
                </div>
                
                @if($internalStats['pending_returns'] > 0)
                <div style="background-color: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px; padding: 12px; margin-bottom: 16px; display: flex; align-items: center; gap: 10px;">
                    <span class="material-symbols-outlined" style="font-size: 20px; color: #d97706;">warning</span>
                    <div>
                        <p style="font-size: 12px; font-weight: 500; color: #92400e; margin: 0;">{{ $internalStats['pending_returns'] }} pending return(s)</p>
                        <p style="font-size: 11px; color: #a16207; margin: 2px 0 0 0;">Assets currently checked out</p>
                    </div>
                </div>
                @endif

                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <th style="text-align: left; padding: 10px 0; font-size: 11px; font-weight: 600; color: #6b7280;">Asset</th>
                            <th style="text-align: left; padding: 10px 0; font-size: 11px; font-weight: 600; color: #6b7280;">Employee</th>
                            <th style="text-align: center; padding: 10px 0; font-size: 11px; font-weight: 600; color: #6b7280;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentMovements->take(4) as $movement)
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 12px 0;"><span style="font-size: 12px; font-weight: 500; color: #111827;">{{ $movement->asset?->asset_tag ?? '-' }}</span></td>
                            <td style="padding: 12px 0;"><span style="font-size: 12px; color: #6b7280;">{{ $movement->employee?->full_name ?? '-' }}</span></td>
                            <td style="padding: 12px 0; text-align: center;">
                                @if($movement->actual_return_date)
                                <span style="display: inline-block; padding: 4px 10px; font-size: 10px; font-weight: 500; border-radius: 9999px; background-color: #10b98115; color: #10b981;">Returned</span>
                                @else
                                <span style="display: inline-block; padding: 4px 10px; font-size: 10px; font-weight: 500; border-radius: 9999px; background-color: #f59e0b15; color: #f59e0b;">Out</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="padding: 40px 0; text-align: center;"><span style="font-size: 12px; color: #9ca3af;">No movements found</span></td>
                        </tr>
                        @endforelse
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
                legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 16 } }
            },
            cutout: '65%'
        }
    });
});
</script>
@endpush
@endsection
