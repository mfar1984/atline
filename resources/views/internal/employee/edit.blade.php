@extends('layouts.app')

@section('title', 'Edit Employee')

@section('page-title', 'Employees')

@section('content')
<div style="background-color: #ffffff; border: 1px solid #e5e7eb;">
    <!-- Header -->
    <div style="padding: 16px 24px; border-bottom: 1px solid #e5e7eb;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h2 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0; font-family: Poppins, sans-serif;">Edit Employee</h2>
                <p style="font-size: 12px; color: #6b7280; margin-top: 4px;">{{ $employee->full_name }}</p>
            </div>
            <a href="{{ route('internal.employee.index') }}" 
               style="display: inline-flex; align-items: center; gap: 8px; padding: 0 12px; min-height: 32px; background-color: #6b7280; color: white; font-size: 11px; font-weight: 500; border-radius: 4px; text-decoration: none;">
                <span class="material-symbols-outlined" style="font-size: 14px;">arrow_back</span>
                BACK
            </a>
        </div>
    </div>

    <div style="padding: 24px;">
        <!-- Timeline Wizard -->
        <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 32px;">
            @php
                $steps = [
                    ['num' => 1, 'title' => 'Employee Info', 'icon' => 'person'],
                    ['num' => 2, 'title' => 'Contact', 'icon' => 'call'],
                    ['num' => 3, 'title' => 'Education', 'icon' => 'school'],
                    ['num' => 4, 'title' => 'Staff Data', 'icon' => 'work'],
                    ['num' => 5, 'title' => 'Attachments', 'icon' => 'attach_file'],
                    ['num' => 6, 'title' => 'Account', 'icon' => 'account_circle'],
                ];
            @endphp
            @foreach($steps as $index => $step)
                <div style="display: flex; align-items: center;">
                    <div class="step-item" data-step="{{ $step['num'] }}" style="display: flex; flex-direction: column; align-items: center; cursor: pointer;">
                        <div class="step-circle" id="step-circle-{{ $step['num'] }}" 
                             style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600; transition: all 0.3s; {{ $step['num'] == 1 ? 'background-color: #3b82f6; color: white;' : 'background-color: #e5e7eb; color: #6b7280;' }}">
                            <span class="material-symbols-outlined" style="font-size: 18px;">{{ $step['icon'] }}</span>
                        </div>
                        <span class="step-title" id="step-title-{{ $step['num'] }}" 
                              style="font-size: 10px; margin-top: 6px; font-weight: 500; {{ $step['num'] == 1 ? 'color: #3b82f6;' : 'color: #6b7280;' }}">{{ $step['title'] }}</span>
                    </div>
                    @if($index < count($steps) - 1)
                        <div class="step-line" id="step-line-{{ $step['num'] }}" 
                             style="width: 60px; height: 2px; margin: 0 8px; margin-bottom: 20px; background-color: #e5e7eb; transition: all 0.3s;"></div>
                    @endif
                </div>
            @endforeach
        </div>

        <form id="employeeForm" action="{{ route('internal.employee.update', $employee) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- Step 1: Employee Info -->
            <div class="step-content" id="step-1" style="display: block;">
                <div style="background-color: #f9fafb; border-radius: 8px; padding: 24px; border: 1px solid #f3f4f6;">
                    <h3 style="font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #3b82f6;">person</span>
                        Employee Information
                    </h3>
                    
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Full Name <span style="color: #ef4444;">*</span></label>
                            <input type="text" name="full_name" value="{{ old('full_name', $employee->full_name) }}" required
                                   style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;"
                                   placeholder="Enter full name">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">IC Number <span style="color: #ef4444;">*</span></label>
                            <input type="text" name="ic_number" value="{{ old('ic_number', $employee->ic_number) }}" required
                                   style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;"
                                   placeholder="e.g., 900101-01-1234">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Birthday</label>
                            <input type="date" name="birthday" value="{{ old('birthday', $employee->birthday?->format('Y-m-d')) }}"
                                   style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;">
                        </div>
                    </div>

                    <!-- Current Address -->
                    <h4 style="font-size: 12px; font-weight: 600; color: #374151; margin: 24px 0 12px 0; padding-top: 16px; border-top: 1px solid #e5e7eb;">Current Address</h4>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 16px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Address Line 1</label>
                            <input type="text" name="current_address_1" value="{{ old('current_address_1', $employee->current_address_1) }}"
                                   style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;"
                                   placeholder="Street address">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Address Line 2</label>
                            <input type="text" name="current_address_2" value="{{ old('current_address_2', $employee->current_address_2) }}"
                                   style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;"
                                   placeholder="Apartment, suite, etc.">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Postcode</label>
                            <input type="text" name="current_postcode" value="{{ old('current_postcode', $employee->current_postcode) }}"
                                   style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;"
                                   placeholder="e.g., 50000">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">District</label>
                            <input type="text" name="current_district" value="{{ old('current_district', $employee->current_district) }}"
                                   style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;"
                                   placeholder="e.g., Kuala Lumpur">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">State</label>
                            <select name="current_state" style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;">
                                <option value="">Select State</option>
                                @foreach(['Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang', 'Perak', 'Perlis', 'Pulau Pinang', 'Sabah', 'Sarawak', 'Selangor', 'Terengganu', 'W.P. Kuala Lumpur', 'W.P. Labuan', 'W.P. Putrajaya'] as $state)
                                    <option value="{{ $state }}" {{ $employee->current_state == $state ? 'selected' : '' }}>{{ $state }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Country</label>
                            <input type="text" name="current_country" value="{{ old('current_country', $employee->current_country ?? 'Malaysia') }}"
                                   style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;">
                        </div>
                    </div>

                    <!-- Correspondence Address -->
                    <div style="display: flex; align-items: center; gap: 8px; margin: 24px 0 12px 0; padding-top: 16px; border-top: 1px solid #e5e7eb;">
                        <h4 style="font-size: 12px; font-weight: 600; color: #374151; margin: 0;">Correspondence Address</h4>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #6b7280; cursor: pointer;">
                            <input type="checkbox" id="sameAddress" style="width: 16px; height: 16px;">
                            Same as current address
                        </label>
                    </div>
                    <div id="correspondenceFields">
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 16px;">
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Address Line 1</label>
                                <input type="text" name="correspondence_address_1" id="corr_address_1" value="{{ old('correspondence_address_1', $employee->correspondence_address_1) }}"
                                       style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Address Line 2</label>
                                <input type="text" name="correspondence_address_2" id="corr_address_2" value="{{ old('correspondence_address_2', $employee->correspondence_address_2) }}"
                                       style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;">
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Postcode</label>
                                <input type="text" name="correspondence_postcode" id="corr_postcode" value="{{ old('correspondence_postcode', $employee->correspondence_postcode) }}"
                                       style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">District</label>
                                <input type="text" name="correspondence_district" id="corr_district" value="{{ old('correspondence_district', $employee->correspondence_district) }}"
                                       style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">State</label>
                                <select name="correspondence_state" id="corr_state" style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;">
                                    <option value="">Select State</option>
                                    @foreach(['Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang', 'Perak', 'Perlis', 'Pulau Pinang', 'Sabah', 'Sarawak', 'Selangor', 'Terengganu', 'W.P. Kuala Lumpur', 'W.P. Labuan', 'W.P. Putrajaya'] as $state)
                                        <option value="{{ $state }}" {{ $employee->correspondence_state == $state ? 'selected' : '' }}>{{ $state }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Country</label>
                                <input type="text" name="correspondence_country" id="corr_country" value="{{ old('correspondence_country', $employee->correspondence_country ?? 'Malaysia') }}"
                                       style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Contact Information -->
            <div class="step-content" id="step-2" style="display: none;">
                <div style="background-color: #f9fafb; border-radius: 8px; padding: 24px; border: 1px solid #f3f4f6;">
                    <h3 style="font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #3b82f6;">call</span>
                        Contact Information
                    </h3>
                    
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Telephone</label>
                            <input type="text" name="telephone" value="{{ old('telephone', $employee->telephone) }}"
                                   style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;"
                                   placeholder="e.g., 012-3456789">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">WhatsApp</label>
                            <input type="text" name="whatsapp" value="{{ old('whatsapp', $employee->whatsapp) }}"
                                   style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;"
                                   placeholder="e.g., 012-3456789">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Email</label>
                            <input type="email" name="email" value="{{ old('email', $employee->email) }}"
                                   style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;"
                                   placeholder="e.g., john@example.com">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr; gap: 16px; margin-bottom: 20px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Marital Status</label>
                            <select name="marital_status" style="width: 100%; max-width: 200px; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;">
                                <option value="single" {{ $employee->marital_status == 'single' ? 'selected' : '' }}>Single</option>
                                <option value="married" {{ $employee->marital_status == 'married' ? 'selected' : '' }}>Married</option>
                                <option value="divorced" {{ $employee->marital_status == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                <option value="widowed" {{ $employee->marital_status == 'widowed' ? 'selected' : '' }}>Widowed</option>
                            </select>
                        </div>
                    </div>

                    <!-- Emergency Contact -->
                    <h4 style="font-size: 12px; font-weight: 600; color: #374151; margin: 24px 0 12px 0; padding-top: 16px; border-top: 1px solid #e5e7eb;">Emergency Contact</h4>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Contact Name</label>
                            <input type="text" name="emergency_name" value="{{ old('emergency_name', $employee->emergency_name) }}"
                                   style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;"
                                   placeholder="Emergency contact name">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Contact Telephone</label>
                            <input type="text" name="emergency_telephone" value="{{ old('emergency_telephone', $employee->emergency_telephone) }}"
                                   style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;"
                                   placeholder="e.g., 012-3456789">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Relationship</label>
                            <select name="emergency_relationship" style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;">
                                <option value="">Select Relationship</option>
                                @foreach(['spouse', 'parent', 'sibling', 'child', 'friend', 'other'] as $rel)
                                    <option value="{{ $rel }}" {{ $employee->emergency_relationship == $rel ? 'selected' : '' }}>{{ ucfirst($rel) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Education -->
            <div class="step-content" id="step-3" style="display: none;">
                <div style="background-color: #f9fafb; border-radius: 8px; padding: 24px; border: 1px solid #f3f4f6;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                        <h3 style="font-size: 13px; font-weight: 600; color: #111827; margin: 0; display: flex; align-items: center; gap: 8px;">
                            <span class="material-symbols-outlined" style="font-size: 18px; color: #3b82f6;">school</span>
                            Education History
                        </h3>
                        <button type="button" onclick="addEducation()" 
                                style="display: inline-flex; align-items: center; gap: 6px; padding: 0 12px; min-height: 32px; background-color: #10b981; color: white; font-size: 11px; font-weight: 500; border-radius: 4px; border: none; cursor: pointer;">
                            <span class="material-symbols-outlined" style="font-size: 14px;">add</span>
                            ADD EDUCATION
                        </button>
                    </div>
                    
                    <div id="educationContainer">
                        @foreach($employee->educations as $index => $education)
                        <div class="education-entry" id="education-existing-{{ $education->id }}" style="background-color: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                                <span style="font-size: 12px; font-weight: 600; color: #374151;">Education #{{ $index + 1 }}</span>
                                <button type="button" onclick="removeExistingEducation({{ $education->id }})" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; background-color: #fee2e2; color: #dc2626; font-size: 10px; font-weight: 500; border-radius: 4px; border: none; cursor: pointer;">
                                    <span class="material-symbols-outlined" style="font-size: 14px;">delete</span>
                                    REMOVE
                                </button>
                            </div>
                            <input type="hidden" name="existing_educations[{{ $education->id }}][id]" value="{{ $education->id }}">
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Level <span style="color: #ef4444;">*</span></label>
                                    <select name="existing_educations[{{ $education->id }}][level]" required style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;">
                                        <option value="">Select Level</option>
                                        @foreach(['SPM', 'STPM', 'Diploma', 'Degree', 'Master', 'PhD', 'Professional Cert', 'Other'] as $level)
                                            <option value="{{ $level }}" {{ $education->level == $level ? 'selected' : '' }}>{{ $level }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="grid-column: span 2;">
                                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Institution <span style="color: #ef4444;">*</span></label>
                                    <input type="text" name="existing_educations[{{ $education->id }}][institution]" value="{{ $education->institution }}" required style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Field of Study</label>
                                    <input type="text" name="existing_educations[{{ $education->id }}][field_of_study]" value="{{ $education->field_of_study }}" style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Year Start</label>
                                    <input type="number" name="existing_educations[{{ $education->id }}][year_start]" value="{{ $education->year_start }}" min="1950" max="2030" style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Year End</label>
                                    <input type="number" name="existing_educations[{{ $education->id }}][year_end]" value="{{ $education->year_end }}" min="1950" max="2030" style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Grade/CGPA</label>
                                    <input type="text" name="existing_educations[{{ $education->id }}][grade]" value="{{ $education->grade }}" style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div id="noEducation" style="text-align: center; padding: 40px 0; color: #9ca3af; {{ $employee->educations->count() > 0 ? 'display: none;' : '' }}">
                        <span class="material-symbols-outlined" style="font-size: 48px; opacity: 0.5;">school</span>
                        <p style="font-size: 12px; margin-top: 8px;">No education records added yet</p>
                        <p style="font-size: 11px;">Click "ADD EDUCATION" to add education history</p>
                    </div>
                </div>
            </div>
            <input type="hidden" name="deleted_educations" id="deletedEducations" value="">

            <!-- Step 4: Staff Data -->
            <div class="step-content" id="step-4" style="display: none;">
                <div style="background-color: #f9fafb; border-radius: 8px; padding: 24px; border: 1px solid #f3f4f6;">
                    <h3 style="font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #3b82f6;">work</span>
                        Staff Data
                    </h3>
                    
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 20px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Position</label>
                            <input type="text" name="position" value="{{ old('position', $employee->position) }}"
                                   style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;"
                                   placeholder="e.g., Software Engineer">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Salary (RM)</label>
                            <input type="number" name="salary" step="0.01" value="{{ old('salary', $employee->salary) }}"
                                   style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;"
                                   placeholder="e.g., 5000.00">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Join Date</label>
                            <input type="date" name="join_date" value="{{ old('join_date', $employee->join_date?->format('Y-m-d')) }}"
                                   style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Working Hours</label>
                            <input type="text" name="time_works" value="{{ old('time_works', $employee->time_works) }}"
                                   style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;"
                                   placeholder="e.g., 9:00 AM - 6:00 PM">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Status</label>
                            <select name="status" style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;">
                                <option value="active" {{ $employee->status == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $employee->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="resigned" {{ $employee->status == 'resigned' ? 'selected' : '' }}>Resigned</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 5: Attachments -->
            <div class="step-content" id="step-5" style="display: none;">
                <div style="background-color: #f9fafb; border-radius: 8px; padding: 24px; border: 1px solid #f3f4f6;">
                    <h3 style="font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #3b82f6;">attach_file</span>
                        Attachments
                    </h3>
                    
                    @if($employee->attachments->count() > 0)
                    <div style="margin-bottom: 20px;">
                        <h4 style="font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 12px;">Existing Attachments</h4>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                            @foreach($employee->attachments as $attachment)
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background-color: white; border: 1px solid #e5e7eb; border-radius: 6px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span class="material-symbols-outlined" style="font-size: 20px; color: #6b7280;">description</span>
                                    <div>
                                        <p style="font-size: 11px; font-weight: 500; color: #374151;">{{ ucfirst(str_replace('_', ' ', $attachment->type)) }}</p>
                                        <p style="font-size: 10px; color: #9ca3af;">{{ $attachment->file_name }}</p>
                                    </div>
                                </div>
                                <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" style="padding: 4px 8px; background-color: #3b82f6; color: white; font-size: 10px; border-radius: 4px; text-decoration: none;">VIEW</a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    <h4 style="font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 12px;">Upload New Attachments</h4>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                        <!-- Front IC -->
                        <div style="border: 2px dashed #d1d5db; border-radius: 8px; padding: 20px; text-align: center;">
                            <span class="material-symbols-outlined" style="font-size: 32px; color: #9ca3af;">badge</span>
                            <p style="font-size: 12px; font-weight: 500; color: #374151; margin: 8px 0 4px 0;">Front IC</p>
                            <p style="font-size: 10px; color: #9ca3af; margin-bottom: 12px;">Upload front side of IC</p>
                            <input type="file" name="front_ic" id="front_ic" accept="image/*,.pdf" style="display: none;">
                            <label for="front_ic" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background-color: #3b82f6; color: white; font-size: 10px; font-weight: 500; border-radius: 4px; cursor: pointer;">
                                <span class="material-symbols-outlined" style="font-size: 14px;">upload</span>
                                CHOOSE FILE
                            </label>
                            <p id="front_ic_name" style="font-size: 10px; color: #10b981; margin-top: 8px;"></p>
                        </div>

                        <!-- Back IC -->
                        <div style="border: 2px dashed #d1d5db; border-radius: 8px; padding: 20px; text-align: center;">
                            <span class="material-symbols-outlined" style="font-size: 32px; color: #9ca3af;">badge</span>
                            <p style="font-size: 12px; font-weight: 500; color: #374151; margin: 8px 0 4px 0;">Back IC</p>
                            <p style="font-size: 10px; color: #9ca3af; margin-bottom: 12px;">Upload back side of IC</p>
                            <input type="file" name="back_ic" id="back_ic" accept="image/*,.pdf" style="display: none;">
                            <label for="back_ic" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background-color: #3b82f6; color: white; font-size: 10px; font-weight: 500; border-radius: 4px; cursor: pointer;">
                                <span class="material-symbols-outlined" style="font-size: 14px;">upload</span>
                                CHOOSE FILE
                            </label>
                            <p id="back_ic_name" style="font-size: 10px; color: #10b981; margin-top: 8px;"></p>
                        </div>

                        <!-- Resume -->
                        <div style="border: 2px dashed #d1d5db; border-radius: 8px; padding: 20px; text-align: center;">
                            <span class="material-symbols-outlined" style="font-size: 32px; color: #9ca3af;">description</span>
                            <p style="font-size: 12px; font-weight: 500; color: #374151; margin: 8px 0 4px 0;">Resume</p>
                            <p style="font-size: 10px; color: #9ca3af; margin-bottom: 12px;">Upload resume/CV</p>
                            <input type="file" name="resume" id="resume" accept=".pdf,.doc,.docx" style="display: none;">
                            <label for="resume" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background-color: #3b82f6; color: white; font-size: 10px; font-weight: 500; border-radius: 4px; cursor: pointer;">
                                <span class="material-symbols-outlined" style="font-size: 14px;">upload</span>
                                CHOOSE FILE
                            </label>
                            <p id="resume_name" style="font-size: 10px; color: #10b981; margin-top: 8px;"></p>
                        </div>

                        <!-- Certificate -->
                        <div style="border: 2px dashed #d1d5db; border-radius: 8px; padding: 20px; text-align: center;">
                            <span class="material-symbols-outlined" style="font-size: 32px; color: #9ca3af;">workspace_premium</span>
                            <p style="font-size: 12px; font-weight: 500; color: #374151; margin: 8px 0 4px 0;">Certificate</p>
                            <p style="font-size: 10px; color: #9ca3af; margin-bottom: 12px;">Upload certificates</p>
                            <input type="file" name="certificate" id="certificate" accept="image/*,.pdf" style="display: none;">
                            <label for="certificate" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background-color: #3b82f6; color: white; font-size: 10px; font-weight: 500; border-radius: 4px; cursor: pointer;">
                                <span class="material-symbols-outlined" style="font-size: 14px;">upload</span>
                                CHOOSE FILE
                            </label>
                            <p id="certificate_name" style="font-size: 10px; color: #10b981; margin-top: 8px;"></p>
                        </div>

                        <!-- Offer Letter -->
                        <div style="border: 2px dashed #d1d5db; border-radius: 8px; padding: 20px; text-align: center; grid-column: span 2;">
                            <span class="material-symbols-outlined" style="font-size: 32px; color: #9ca3af;">mail</span>
                            <p style="font-size: 12px; font-weight: 500; color: #374151; margin: 8px 0 4px 0;">Offer Letter</p>
                            <p style="font-size: 10px; color: #9ca3af; margin-bottom: 12px;">Upload signed offer letter</p>
                            <input type="file" name="offer_letter" id="offer_letter" accept=".pdf,.doc,.docx" style="display: none;">
                            <label for="offer_letter" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background-color: #3b82f6; color: white; font-size: 10px; font-weight: 500; border-radius: 4px; cursor: pointer;">
                                <span class="material-symbols-outlined" style="font-size: 14px;">upload</span>
                                CHOOSE FILE
                            </label>
                            <p id="offer_letter_name" style="font-size: 10px; color: #10b981; margin-top: 8px;"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 6: Account -->
            <div class="step-content" id="step-6" style="display: none;">
                <div style="background-color: #f9fafb; border-radius: 8px; padding: 24px; border: 1px solid #f3f4f6;">
                    <h3 style="font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 20px 0; display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #3b82f6;">account_circle</span>
                        User Account
                    </h3>
                    
                    @if($employee->user)
                    <div style="background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <span class="material-symbols-outlined" style="font-size: 20px; color: #10b981;">check_circle</span>
                            <span style="font-size: 12px; font-weight: 600; color: #065f46;">User Account Exists</span>
                        </div>
                        <p style="font-size: 11px; color: #047857;">This employee has an existing user account: {{ $employee->user->email }}</p>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Email (Username)</label>
                            <input type="email" value="{{ $employee->user->email }}" disabled
                                   style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none; background-color: #f3f4f6;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Role</label>
                            <select name="role_id" style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;">
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ $employee->user->role_id == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div style="margin-top: 16px;">
                        <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">New Password (leave blank to keep current)</label>
                        <input type="password" name="password"
                               style="width: 100%; max-width: 300px; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;"
                               placeholder="Enter new password to change">
                    </div>
                    @else
                    <!-- Account Type Selection -->
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="account_type" value="none" id="accountTypeNone" checked style="width: 16px; height: 16px;">
                                <span style="font-size: 12px; font-weight: 500; color: #374151;">No user account</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="account_type" value="create" id="accountTypeCreate" style="width: 16px; height: 16px;">
                                <span style="font-size: 12px; font-weight: 500; color: #374151;">Create new user account</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="account_type" value="link" id="accountTypeLink" style="width: 16px; height: 16px;">
                                <span style="font-size: 12px; font-weight: 500; color: #374151;">Link to existing user</span>
                            </label>
                        </div>
                    </div>

                    <!-- Create New Account Fields -->
                    <div id="createAccountFields" style="display: none;">
                        <input type="hidden" name="create_account" id="createAccountHidden" value="0">
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Username (Email) <span style="color: #ef4444;">*</span></label>
                                <input type="email" name="username" id="accountUsername"
                                       style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;"
                                       placeholder="e.g., john@company.com">
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Password <span style="color: #ef4444;">*</span></label>
                                <input type="password" name="new_password" id="accountPassword"
                                       style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;"
                                       placeholder="Minimum 6 characters">
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Role</label>
                                <select name="role_id" id="createRoleId" style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;">
                                    <option value="">Select Role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Link Existing User Fields -->
                    <div id="linkUserFields" style="display: none;">
                        <input type="hidden" name="link_existing_user" id="linkExistingUserHidden" value="0">
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Select Existing User <span style="color: #ef4444;">*</span></label>
                                <select name="existing_user_id" id="existingUserId" style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;">
                                    <option value="">Select User</option>
                                    @foreach($availableUsers as $user)
                                        <option value="{{ $user->id }}" data-email="{{ $user->email }}" data-role="{{ $user->role_id }}">{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                                @if($availableUsers->isEmpty())
                                    <p style="font-size: 10px; color: #f59e0b; margin-top: 4px;">No available users. All users are already linked to employees or clients.</p>
                                @endif
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Role</label>
                                <select name="role_id" id="linkRoleId" style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;">
                                    <option value="">Select Role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div id="selectedUserInfo" style="display: none; margin-top: 12px; padding: 12px; background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span class="material-symbols-outlined" style="font-size: 18px; color: #3b82f6;">person</span>
                                <span id="selectedUserName" style="font-size: 12px; font-weight: 500; color: #1e40af;"></span>
                            </div>
                            <p id="selectedUserEmail" style="font-size: 11px; color: #3b82f6; margin-top: 4px; margin-left: 26px;"></p>
                        </div>
                    </div>

                    <div id="noAccountMessage" style="text-align: center; padding: 40px 0; color: #9ca3af;">
                        <span class="material-symbols-outlined" style="font-size: 48px; opacity: 0.5;">no_accounts</span>
                        <p style="font-size: 12px; margin-top: 8px;">No user account will be created</p>
                        <p style="font-size: 11px;">Select an option above to create or link a user account</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 24px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                <button type="button" id="prevBtn" onclick="changeStep(-1)" 
                        style="display: none; align-items: center; gap: 6px; padding: 0 16px; min-height: 36px; background-color: #6b7280; color: white; font-size: 11px; font-weight: 500; border-radius: 4px; border: none; cursor: pointer;">
                    <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                    PREVIOUS
                </button>
                <div></div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" id="nextBtn" onclick="changeStep(1)" 
                            style="display: inline-flex; align-items: center; gap: 6px; padding: 0 16px; min-height: 36px; background-color: #3b82f6; color: white; font-size: 11px; font-weight: 500; border-radius: 4px; border: none; cursor: pointer;">
                        NEXT
                        <span class="material-symbols-outlined" style="font-size: 16px;">arrow_forward</span>
                    </button>
                    <button type="submit" id="submitBtn" 
                            style="display: none; align-items: center; gap: 6px; padding: 0 16px; min-height: 36px; background-color: #10b981; color: white; font-size: 11px; font-weight: 500; border-radius: 4px; border: none; cursor: pointer;">
                        <span class="material-symbols-outlined" style="font-size: 16px;">check</span>
                        SAVE CHANGES
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let currentStep = 1;
const totalSteps = 6;
let educationCount = {{ $employee->educations->count() }};
let deletedEducations = [];

function changeStep(direction) {
    const newStep = currentStep + direction;
    if (newStep < 1 || newStep > totalSteps) return;
    
    // Validate current step before moving forward
    if (direction > 0 && !validateStep(currentStep)) return;
    
    goToStep(newStep);
}

function goToStep(step) {
    // Hide current step
    document.getElementById('step-' + currentStep).style.display = 'none';
    
    // Update timeline
    for (let i = 1; i <= totalSteps; i++) {
        const circle = document.getElementById('step-circle-' + i);
        const title = document.getElementById('step-title-' + i);
        const line = document.getElementById('step-line-' + i);
        
        if (i < step) {
            // Completed
            circle.style.backgroundColor = '#10b981';
            circle.style.color = 'white';
            title.style.color = '#10b981';
            if (line) line.style.backgroundColor = '#10b981';
        } else if (i === step) {
            // Current
            circle.style.backgroundColor = '#3b82f6';
            circle.style.color = 'white';
            title.style.color = '#3b82f6';
        } else {
            // Future
            circle.style.backgroundColor = '#e5e7eb';
            circle.style.color = '#6b7280';
            title.style.color = '#6b7280';
            if (document.getElementById('step-line-' + (i-1))) {
                document.getElementById('step-line-' + (i-1)).style.backgroundColor = '#e5e7eb';
            }
        }
    }
    
    // Show new step
    document.getElementById('step-' + step).style.display = 'block';
    currentStep = step;
    
    // Update buttons
    document.getElementById('prevBtn').style.display = step > 1 ? 'inline-flex' : 'none';
    document.getElementById('nextBtn').style.display = step < totalSteps ? 'inline-flex' : 'none';
    document.getElementById('submitBtn').style.display = step === totalSteps ? 'inline-flex' : 'none';
}

function validateStep(step) {
    if (step === 1) {
        const fullName = document.querySelector('input[name="full_name"]').value;
        const icNumber = document.querySelector('input[name="ic_number"]').value;
        if (!fullName || !icNumber) {
            alert('Please fill in Full Name and IC Number');
            return false;
        }
    }
    return true;
}

// Same address checkbox
document.getElementById('sameAddress').addEventListener('change', function() {
    if (this.checked) {
        document.getElementById('corr_address_1').value = document.querySelector('input[name="current_address_1"]').value;
        document.getElementById('corr_address_2').value = document.querySelector('input[name="current_address_2"]').value;
        document.getElementById('corr_postcode').value = document.querySelector('input[name="current_postcode"]').value;
        document.getElementById('corr_district').value = document.querySelector('input[name="current_district"]').value;
        document.getElementById('corr_state').value = document.querySelector('select[name="current_state"]').value;
        document.getElementById('corr_country').value = document.querySelector('input[name="current_country"]').value;
    }
});

// Create account checkbox (only if no existing user)
const createAccountCheckbox = document.getElementById('createAccount');
if (createAccountCheckbox) {
    createAccountCheckbox.addEventListener('change', function() {
        document.getElementById('accountFields').style.display = this.checked ? 'block' : 'none';
        document.getElementById('noAccountMessage').style.display = this.checked ? 'none' : 'block';
    });
}

// Account type radio buttons (only if no existing user)
const accountTypeRadios = document.querySelectorAll('input[name="account_type"]');
if (accountTypeRadios.length > 0) {
    accountTypeRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            const createFields = document.getElementById('createAccountFields');
            const linkFields = document.getElementById('linkUserFields');
            const noAccountMsg = document.getElementById('noAccountMessage');
            const createHidden = document.getElementById('createAccountHidden');
            const linkHidden = document.getElementById('linkExistingUserHidden');
            
            // Reset all
            createFields.style.display = 'none';
            linkFields.style.display = 'none';
            noAccountMsg.style.display = 'none';
            createHidden.value = '0';
            linkHidden.value = '0';
            
            if (this.value === 'create') {
                createFields.style.display = 'block';
                createHidden.value = '1';
            } else if (this.value === 'link') {
                linkFields.style.display = 'block';
                linkHidden.value = '1';
            } else {
                noAccountMsg.style.display = 'block';
            }
        });
    });
}

// Existing user selection
const existingUserSelect = document.getElementById('existingUserId');
if (existingUserSelect) {
    existingUserSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const infoDiv = document.getElementById('selectedUserInfo');
        const linkRoleSelect = document.getElementById('linkRoleId');
        
        if (this.value) {
            const email = selectedOption.dataset.email;
            const roleId = selectedOption.dataset.role;
            document.getElementById('selectedUserName').textContent = selectedOption.text.split(' (')[0];
            document.getElementById('selectedUserEmail').textContent = email;
            infoDiv.style.display = 'block';
            
            // Set role if user has one
            if (roleId) {
                linkRoleSelect.value = roleId;
            }
        } else {
            infoDiv.style.display = 'none';
        }
    });
}

// File upload display
['front_ic', 'back_ic', 'resume', 'certificate', 'offer_letter'].forEach(function(id) {
    const el = document.getElementById(id);
    if (el) {
        el.addEventListener('change', function() {
            const nameEl = document.getElementById(id + '_name');
            if (this.files.length > 0) {
                nameEl.textContent = this.files[0].name;
            } else {
                nameEl.textContent = '';
            }
        });
    }
});

// Education functions
function addEducation() {
    educationCount++;
    document.getElementById('noEducation').style.display = 'none';
    
    const container = document.getElementById('educationContainer');
    const html = `
        <div class="education-entry" id="education-${educationCount}" style="background-color: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                <span style="font-size: 12px; font-weight: 600; color: #374151;">New Education</span>
                <button type="button" onclick="removeEducation(${educationCount})" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; background-color: #fee2e2; color: #dc2626; font-size: 10px; font-weight: 500; border-radius: 4px; border: none; cursor: pointer;">
                    <span class="material-symbols-outlined" style="font-size: 14px;">delete</span>
                    REMOVE
                </button>
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Level <span style="color: #ef4444;">*</span></label>
                    <select name="educations[${educationCount}][level]" required style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;">
                        <option value="">Select Level</option>
                        <option value="SPM">SPM</option>
                        <option value="STPM">STPM</option>
                        <option value="Diploma">Diploma</option>
                        <option value="Degree">Degree</option>
                        <option value="Master">Master</option>
                        <option value="PhD">PhD</option>
                        <option value="Professional Cert">Professional Cert</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Institution <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="educations[${educationCount}][institution]" required style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;" placeholder="e.g., Universiti Malaya">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Field of Study</label>
                    <input type="text" name="educations[${educationCount}][field_of_study]" style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;" placeholder="e.g., Computer Science">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Year Start</label>
                    <input type="number" name="educations[${educationCount}][year_start]" min="1950" max="2030" style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;" placeholder="e.g., 2018">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Year End</label>
                    <input type="number" name="educations[${educationCount}][year_end]" min="1950" max="2030" style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;" placeholder="e.g., 2022">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Grade/CGPA</label>
                    <input type="text" name="educations[${educationCount}][grade]" style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; outline: none;" placeholder="e.g., 3.50">
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

function removeEducation(id) {
    document.getElementById('education-' + id).remove();
    checkEducationEmpty();
}

function removeExistingEducation(id) {
    document.getElementById('education-existing-' + id).remove();
    deletedEducations.push(id);
    document.getElementById('deletedEducations').value = deletedEducations.join(',');
    checkEducationEmpty();
}

function checkEducationEmpty() {
    if (document.querySelectorAll('.education-entry').length === 0) {
        document.getElementById('noEducation').style.display = 'block';
    }
}

// Click on timeline step
document.querySelectorAll('.step-item').forEach(function(item) {
    item.addEventListener('click', function() {
        const step = parseInt(this.dataset.step);
        if (step < currentStep) {
            goToStep(step);
        }
    });
});
</script>
@endpush
@endsection
