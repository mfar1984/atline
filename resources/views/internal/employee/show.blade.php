@extends('layouts.app')

@section('title', 'Employee Details')

@section('page-title', 'Employees')

@section('content')
<div style="background-color: #ffffff; border: 1px solid #e5e7eb;">
    <!-- Header -->
    <div style="padding: 16px 24px; border-bottom: 1px solid #e5e7eb;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); display: flex; align-items: center; justify-content: center;">
                    <span style="font-size: 20px; font-weight: 700; color: white;">{{ strtoupper(substr($employee->full_name, 0, 2)) }}</span>
                </div>
                <div>
                    <h2 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0; font-family: Poppins, sans-serif;">{{ $employee->full_name }}</h2>
                    <p style="font-size: 12px; color: #6b7280; margin-top: 2px;">{{ $employee->position ?? 'No Position' }} • {{ $employee->ic_number }}</p>
                </div>
            </div>
            <div style="display: flex; gap: 8px;">
                @permission('internal_employee.update')
                <a href="{{ route('internal.employee.edit', $employee) }}" 
                   style="display: inline-flex; align-items: center; gap: 6px; padding: 0 12px; min-height: 32px; background-color: #f59e0b; color: white; font-size: 11px; font-weight: 500; border-radius: 4px; text-decoration: none;">
                    <span class="material-symbols-outlined" style="font-size: 14px;">edit</span>
                    EDIT
                </a>
                @endpermission
                <a href="{{ route('internal.employee.index') }}" 
                   style="display: inline-flex; align-items: center; gap: 6px; padding: 0 12px; min-height: 32px; background-color: #6b7280; color: white; font-size: 11px; font-weight: 500; border-radius: 4px; text-decoration: none;">
                    <span class="material-symbols-outlined" style="font-size: 14px;">arrow_back</span>
                    BACK
                </a>
            </div>
        </div>
    </div>

    <div style="padding: 24px;">
        <!-- Status Badge -->
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 24px;">
            @php
                $statusColors = [
                    'active' => ['bg' => '#dcfce7', 'text' => '#166534'],
                    'inactive' => ['bg' => '#f3f4f6', 'text' => '#374151'],
                    'resigned' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                ];
                $color = $statusColors[$employee->status] ?? $statusColors['inactive'];
            @endphp
            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; background-color: {{ $color['bg'] }}; color: {{ $color['text'] }}; font-size: 11px; font-weight: 600; border-radius: 9999px; height: 32px; box-sizing: border-box;">
                <span style="width: 8px; height: 8px; border-radius: 50%; background-color: {{ $color['text'] }}; flex-shrink: 0;"></span>
                {{ ucfirst($employee->status) }}
            </span>
            @if($employee->user)
            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; background-color: #dbeafe; color: #1e40af; font-size: 11px; font-weight: 600; border-radius: 9999px; height: 32px; box-sizing: border-box;">
                <span class="material-symbols-outlined" style="font-size: 14px; line-height: 1;">verified_user</span>
                Has Account
            </span>
            @endif
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
            <!-- Left Column -->
            <div>
                <!-- Personal Information -->
                <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; border: 1px solid #f3f4f6; margin-bottom: 20px;">
                    <h3 style="font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #3b82f6;">person</span>
                        Personal Information
                    </h3>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                        <div>
                            <p style="font-size: 10px; color: #6b7280; margin: 0;">Full Name</p>
                            <p style="font-size: 12px; font-weight: 500; color: #111827; margin: 4px 0 0 0;">{{ $employee->full_name }}</p>
                        </div>
                        <div>
                            <p style="font-size: 10px; color: #6b7280; margin: 0;">IC Number</p>
                            <p style="font-size: 12px; font-weight: 500; color: #111827; margin: 4px 0 0 0;">{{ $employee->ic_number }}</p>
                        </div>
                        <div>
                            <p style="font-size: 10px; color: #6b7280; margin: 0;">Birthday</p>
                            <p style="font-size: 12px; font-weight: 500; color: #111827; margin: 4px 0 0 0;">{{ $employee->birthday ? $employee->birthday->format('d/m/Y') : '-' }}</p>
                        </div>
                        <div>
                            <p style="font-size: 10px; color: #6b7280; margin: 0;">Age</p>
                            <p style="font-size: 12px; font-weight: 500; color: #111827; margin: 4px 0 0 0;">{{ $employee->age ?? '-' }} years</p>
                        </div>
                        <div>
                            <p style="font-size: 10px; color: #6b7280; margin: 0;">Marital Status</p>
                            <p style="font-size: 12px; font-weight: 500; color: #111827; margin: 4px 0 0 0;">{{ ucfirst($employee->marital_status) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; border: 1px solid #f3f4f6; margin-bottom: 20px;">
                    <h3 style="font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #3b82f6;">call</span>
                        Contact Information
                    </h3>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                        <div>
                            <p style="font-size: 10px; color: #6b7280; margin: 0;">Telephone</p>
                            <p style="font-size: 12px; font-weight: 500; color: #111827; margin: 4px 0 0 0;">{{ $employee->telephone ?? '-' }}</p>
                        </div>
                        <div>
                            <p style="font-size: 10px; color: #6b7280; margin: 0;">WhatsApp</p>
                            <p style="font-size: 12px; font-weight: 500; color: #111827; margin: 4px 0 0 0;">{{ $employee->whatsapp ?? '-' }}</p>
                        </div>
                        <div>
                            <p style="font-size: 10px; color: #6b7280; margin: 0;">Email</p>
                            <p style="font-size: 12px; font-weight: 500; color: #111827; margin: 4px 0 0 0;">{{ $employee->email ?? '-' }}</p>
                        </div>
                    </div>
                    
                    <h4 style="font-size: 11px; font-weight: 600; color: #374151; margin: 20px 0 12px 0; padding-top: 16px; border-top: 1px solid #e5e7eb;">Emergency Contact</h4>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                        <div>
                            <p style="font-size: 10px; color: #6b7280; margin: 0;">Name</p>
                            <p style="font-size: 12px; font-weight: 500; color: #111827; margin: 4px 0 0 0;">{{ $employee->emergency_name ?? '-' }}</p>
                        </div>
                        <div>
                            <p style="font-size: 10px; color: #6b7280; margin: 0;">Telephone</p>
                            <p style="font-size: 12px; font-weight: 500; color: #111827; margin: 4px 0 0 0;">{{ $employee->emergency_telephone ?? '-' }}</p>
                        </div>
                        <div>
                            <p style="font-size: 10px; color: #6b7280; margin: 0;">Relationship</p>
                            <p style="font-size: 12px; font-weight: 500; color: #111827; margin: 4px 0 0 0;">{{ ucfirst($employee->emergency_relationship ?? '-') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; border: 1px solid #f3f4f6; margin-bottom: 20px;">
                    <h3 style="font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #3b82f6;">home</span>
                        Address
                    </h3>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                        <div>
                            <h4 style="font-size: 11px; font-weight: 600; color: #374151; margin: 0 0 8px 0;">Current Address</h4>
                            <p style="font-size: 12px; color: #4b5563; margin: 0; line-height: 1.6;">
                                {{ $employee->current_address_1 ?? '' }}<br>
                                {{ $employee->current_address_2 ?? '' }}<br>
                                {{ $employee->current_postcode ?? '' }} {{ $employee->current_district ?? '' }}<br>
                                {{ $employee->current_state ?? '' }}, {{ $employee->current_country ?? 'Malaysia' }}
                            </p>
                        </div>
                        <div>
                            <h4 style="font-size: 11px; font-weight: 600; color: #374151; margin: 0 0 8px 0;">Correspondence Address</h4>
                            <p style="font-size: 12px; color: #4b5563; margin: 0; line-height: 1.6;">
                                {{ $employee->correspondence_address_1 ?? '' }}<br>
                                {{ $employee->correspondence_address_2 ?? '' }}<br>
                                {{ $employee->correspondence_postcode ?? '' }} {{ $employee->correspondence_district ?? '' }}<br>
                                {{ $employee->correspondence_state ?? '' }}, {{ $employee->correspondence_country ?? 'Malaysia' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Education -->
                <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; border: 1px solid #f3f4f6;">
                    <h3 style="font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #3b82f6;">school</span>
                        Education History
                    </h3>
                    @forelse($employee->educations as $edu)
                    <div style="background-color: white; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; margin-bottom: 12px;">
                        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                            <div>
                                <p style="font-size: 12px; font-weight: 600; color: #111827; margin: 0;">{{ $edu->level }}</p>
                                <p style="font-size: 11px; color: #4b5563; margin: 4px 0 0 0;">{{ $edu->institution }}</p>
                                @if($edu->field_of_study)
                                <p style="font-size: 11px; color: #6b7280; margin: 2px 0 0 0;">{{ $edu->field_of_study }}</p>
                                @endif
                            </div>
                            <div style="text-align: right;">
                                <p style="font-size: 11px; color: #6b7280; margin: 0;">{{ $edu->year_start ?? '?' }} - {{ $edu->year_end ?? '?' }}</p>
                                @if($edu->grade)
                                <p style="font-size: 11px; font-weight: 500; color: #10b981; margin: 4px 0 0 0;">Grade: {{ $edu->grade }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <p style="font-size: 11px; color: #9ca3af; text-align: center; padding: 20px 0;">No education records</p>
                    @endforelse
                </div>
            </div>

            <!-- Right Column -->
            <div>
                <!-- Staff Data -->
                <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; border: 1px solid #f3f4f6; margin-bottom: 20px;">
                    <h3 style="font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #3b82f6;">work</span>
                        Staff Data
                    </h3>
                    <div style="display: grid; gap: 12px;">
                        <div style="display: flex; justify-content: space-between; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb;">
                            <span style="font-size: 11px; color: #6b7280;">Position</span>
                            <span style="font-size: 11px; font-weight: 500; color: #111827;">{{ $employee->position ?? '-' }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb;">
                            <span style="font-size: 11px; color: #6b7280;">Salary</span>
                            <span style="font-size: 11px; font-weight: 500; color: #111827;">RM {{ number_format($employee->salary ?? 0, 2) }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb;">
                            <span style="font-size: 11px; color: #6b7280;">Join Date</span>
                            <span style="font-size: 11px; font-weight: 500; color: #111827;">{{ $employee->join_date ? $employee->join_date->format('d/m/Y') : '-' }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb;">
                            <span style="font-size: 11px; color: #6b7280;">Years of Service</span>
                            <span style="font-size: 11px; font-weight: 500; color: #111827;">{{ $employee->years_of_service }} years</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-size: 11px; color: #6b7280;">Working Hours</span>
                            <span style="font-size: 11px; font-weight: 500; color: #111827;">{{ $employee->time_works ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- User Account -->
                @if($employee->user)
                <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; border: 1px solid #f3f4f6; margin-bottom: 20px;">
                    <h3 style="font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #3b82f6;">account_circle</span>
                        User Account
                    </h3>
                    <div style="display: grid; gap: 12px;">
                        <div style="display: flex; justify-content: space-between; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb;">
                            <span style="font-size: 11px; color: #6b7280;">Username</span>
                            <span style="font-size: 11px; font-weight: 500; color: #111827;">{{ $employee->user->email }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-size: 11px; color: #6b7280;">Role</span>
                            <span style="font-size: 11px; font-weight: 500; color: #111827;">{{ $employee->user->role?->name ?? 'No Role' }}</span>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Attachments -->
                <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; border: 1px solid #f3f4f6;">
                    <h3 style="font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #3b82f6;">attach_file</span>
                        Attachments
                    </h3>
                    @forelse($employee->attachments as $attachment)
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px; background-color: white; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 8px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span class="material-symbols-outlined" style="font-size: 20px; color: #6b7280;">description</span>
                            <div>
                                <p style="font-size: 11px; font-weight: 500; color: #111827; margin: 0;">{{ ucwords(str_replace('_', ' ', $attachment->type)) }}</p>
                                <p style="font-size: 10px; color: #9ca3af; margin: 2px 0 0 0;">{{ $attachment->file_name }}</p>
                            </div>
                        </div>
                        <a href="{{ route('internal.employee.attachment.download', $attachment) }}" 
                           style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; background-color: #3b82f6; color: white; border-radius: 4px; text-decoration: none;">
                            <span class="material-symbols-outlined" style="font-size: 16px;">download</span>
                        </a>
                    </div>
                    @empty
                    <p style="font-size: 11px; color: #9ca3af; text-align: center; padding: 20px 0;">No attachments</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
