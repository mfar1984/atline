<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeEducation;
use App\Models\EmployeeAttachment;
use App\Models\User;
use App\Models\Role;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['user', 'educations']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('ic_number', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $employees = $query->orderByDesc('created_at')->paginate(\App\Models\SystemSetting::paginationSize())->withQueryString();

        return view('internal.employee.index', compact('employees'));
    }

    public function create()
    {
        $roles = Role::where('is_active', true)->get();
        
        // Get users not linked to any employee or client
        $availableUsers = User::whereDoesntHave('employee')
            ->whereNotIn('id', \App\Models\Client::whereNotNull('user_id')->pluck('user_id'))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('internal.employee.create', compact('roles', 'availableUsers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Page 1
            'full_name' => 'required|string|max:255',
            'ic_number' => 'required|string|unique:employees,ic_number',
            'birthday' => 'nullable|date',
            'current_address_1' => 'nullable|string',
            'current_address_2' => 'nullable|string',
            'current_postcode' => 'nullable|string',
            'current_district' => 'nullable|string',
            'current_state' => 'nullable|string',
            'current_country' => 'nullable|string',
            'correspondence_address_1' => 'nullable|string',
            'correspondence_address_2' => 'nullable|string',
            'correspondence_postcode' => 'nullable|string',
            'correspondence_district' => 'nullable|string',
            'correspondence_state' => 'nullable|string',
            'correspondence_country' => 'nullable|string',
            // Page 2
            'telephone' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'email' => 'nullable|email',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'emergency_name' => 'nullable|string',
            'emergency_telephone' => 'nullable|string',
            'emergency_relationship' => 'nullable|string',
            // Page 3
            'educations' => 'nullable|array',
            'educations.*.level' => 'required|string',
            'educations.*.institution' => 'required|string',
            'educations.*.field_of_study' => 'nullable|string',
            'educations.*.year_start' => 'nullable|integer',
            'educations.*.year_end' => 'nullable|integer',
            'educations.*.grade' => 'nullable|string',
            // Page 4
            'salary' => 'nullable|numeric',
            'position' => 'nullable|string',
            'join_date' => 'nullable|date',
            'time_works' => 'nullable|string',
            // Page 5
            'front_ic' => 'nullable|file|max:5120',
            'back_ic' => 'nullable|file|max:5120',
            'resume' => 'nullable|file|max:5120',
            'certificate' => 'nullable|file|max:5120',
            'offer_letter' => 'nullable|file|max:5120',
            // Page 6
            'create_account' => 'nullable|boolean',
            'link_existing_user' => 'nullable|boolean',
            'existing_user_id' => ['nullable', 'exists:users,id', function ($attribute, $value, $fail) {
                if ($value) {
                    // Check if user is already linked to an employee
                    if (\App\Models\Employee::where('user_id', $value)->exists()) {
                        $fail('This user is already linked to another employee.');
                    }
                    // Check if user is already linked to a client
                    if (\App\Models\Client::where('user_id', $value)->exists()) {
                        $fail('This user is already linked to a client.');
                    }
                }
            }],
            'username' => 'nullable|required_if:create_account,1|string|unique:users,email',
            'password' => 'nullable|required_if:create_account,1|string|min:6',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        DB::beginTransaction();
        try {
            // Create user account if requested OR link existing user
            $userId = null;
            if ($request->link_existing_user && $request->existing_user_id) {
                // Link to existing user
                $userId = $request->existing_user_id;
                // Update role if provided
                if ($request->role_id) {
                    User::where('id', $userId)->update(['role_id' => $request->role_id]);
                }
            } elseif ($request->create_account && $request->username && $request->password) {
                $user = User::create([
                    'name' => $validated['full_name'],
                    'email' => $validated['username'],
                    'password' => Hash::make($validated['password']),
                    'role_id' => $validated['role_id'] ?? null,
                    'is_active' => true,
                ]);
                $userId = $user->id;
            }

            // Create employee
            $employee = Employee::create([
                'user_id' => $userId,
                'full_name' => $validated['full_name'],
                'ic_number' => $validated['ic_number'],
                'birthday' => $validated['birthday'] ?? null,
                'current_address_1' => $validated['current_address_1'] ?? null,
                'current_address_2' => $validated['current_address_2'] ?? null,
                'current_postcode' => $validated['current_postcode'] ?? null,
                'current_district' => $validated['current_district'] ?? null,
                'current_state' => $validated['current_state'] ?? null,
                'current_country' => $validated['current_country'] ?? 'Malaysia',
                'correspondence_address_1' => $validated['correspondence_address_1'] ?? null,
                'correspondence_address_2' => $validated['correspondence_address_2'] ?? null,
                'correspondence_postcode' => $validated['correspondence_postcode'] ?? null,
                'correspondence_district' => $validated['correspondence_district'] ?? null,
                'correspondence_state' => $validated['correspondence_state'] ?? null,
                'correspondence_country' => $validated['correspondence_country'] ?? 'Malaysia',
                'telephone' => $validated['telephone'] ?? null,
                'whatsapp' => $validated['whatsapp'] ?? null,
                'email' => $validated['email'] ?? null,
                'marital_status' => $validated['marital_status'] ?? 'single',
                'emergency_name' => $validated['emergency_name'] ?? null,
                'emergency_telephone' => $validated['emergency_telephone'] ?? null,
                'emergency_relationship' => $validated['emergency_relationship'] ?? null,
                'salary' => $validated['salary'] ?? null,
                'position' => $validated['position'] ?? null,
                'join_date' => $validated['join_date'] ?? null,
                'time_works' => $validated['time_works'] ?? null,
            ]);

            // Save educations
            if (!empty($validated['educations'])) {
                foreach ($validated['educations'] as $edu) {
                    $employee->educations()->create($edu);
                }
            }

            // Save attachments
            $attachmentTypes = ['front_ic', 'back_ic', 'resume', 'certificate', 'offer_letter'];
            foreach ($attachmentTypes as $type) {
                if ($request->hasFile($type)) {
                    $file = $request->file($type);
                    $path = $file->store('employees/' . $employee->id, 'public');
                    $employee->attachments()->create([
                        'type' => $type,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            DB::commit();
            
            // Log employee creation
            try {
                ActivityLogService::logCreate($employee, 'internal_employee', "Created employee {$employee->full_name}");
            } catch (\Exception $e) {
                \Log::error('Activity logging failed: ' . $e->getMessage());
            }
            
            return redirect()->route('internal.employee.index')
                ->with('success', 'Employee created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create employee: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Employee $employee)
    {
        $employee->load(['user', 'educations', 'attachments']);
        
        // Log view activity
        try {
            ActivityLogService::logView($employee, 'internal_employee', "Viewed employee {$employee->full_name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }
        
        return view('internal.employee.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $employee->load(['user', 'educations', 'attachments']);
        $roles = Role::where('is_active', true)->get();
        
        // Get users not linked to any employee or client (exclude current employee's user)
        $availableUsers = User::whereDoesntHave('employee')
            ->whereNotIn('id', \App\Models\Client::whereNotNull('user_id')->pluck('user_id'))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('internal.employee.edit', compact('employee', 'roles', 'availableUsers'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'ic_number' => 'required|string|unique:employees,ic_number,' . $employee->id,
            'birthday' => 'nullable|date',
            'current_address_1' => 'nullable|string',
            'current_address_2' => 'nullable|string',
            'current_postcode' => 'nullable|string',
            'current_district' => 'nullable|string',
            'current_state' => 'nullable|string',
            'current_country' => 'nullable|string',
            'correspondence_address_1' => 'nullable|string',
            'correspondence_address_2' => 'nullable|string',
            'correspondence_postcode' => 'nullable|string',
            'correspondence_district' => 'nullable|string',
            'correspondence_state' => 'nullable|string',
            'correspondence_country' => 'nullable|string',
            'telephone' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'email' => 'nullable|email',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'emergency_name' => 'nullable|string',
            'emergency_telephone' => 'nullable|string',
            'emergency_relationship' => 'nullable|string',
            'salary' => 'nullable|numeric',
            'position' => 'nullable|string',
            'join_date' => 'nullable|date',
            'time_works' => 'nullable|string',
            'status' => 'nullable|in:active,inactive,resigned',
            // Education
            'educations' => 'nullable|array',
            'educations.*.level' => 'required|string',
            'educations.*.institution' => 'required|string',
            'educations.*.field_of_study' => 'nullable|string',
            'educations.*.year_start' => 'nullable|integer',
            'educations.*.year_end' => 'nullable|integer',
            'educations.*.grade' => 'nullable|string',
            'existing_educations' => 'nullable|array',
            'deleted_educations' => 'nullable|string',
            // Attachments
            'front_ic' => 'nullable|file|max:5120',
            'back_ic' => 'nullable|file|max:5120',
            'resume' => 'nullable|file|max:5120',
            'certificate' => 'nullable|file|max:5120',
            'offer_letter' => 'nullable|file|max:5120',
            // Account
            'create_account' => 'nullable|boolean',
            'link_existing_user' => 'nullable|boolean',
            'existing_user_id' => ['nullable', 'exists:users,id', function ($attribute, $value, $fail) use ($employee) {
                if ($value) {
                    // Check if user is already linked to another employee (not this one)
                    if (\App\Models\Employee::where('user_id', $value)->where('id', '!=', $employee->id)->exists()) {
                        $fail('This user is already linked to another employee.');
                    }
                    // Check if user is already linked to a client
                    if (\App\Models\Client::where('user_id', $value)->exists()) {
                        $fail('This user is already linked to a client.');
                    }
                }
            }],
            'username' => 'nullable|string|email',
            'new_password' => 'nullable|string|min:6',
            'password' => 'nullable|string|min:6',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        DB::beginTransaction();
        try {
            // Update employee basic info
            $employee->update([
                'full_name' => $validated['full_name'],
                'ic_number' => $validated['ic_number'],
                'birthday' => $validated['birthday'] ?? null,
                'current_address_1' => $validated['current_address_1'] ?? null,
                'current_address_2' => $validated['current_address_2'] ?? null,
                'current_postcode' => $validated['current_postcode'] ?? null,
                'current_district' => $validated['current_district'] ?? null,
                'current_state' => $validated['current_state'] ?? null,
                'current_country' => $validated['current_country'] ?? 'Malaysia',
                'correspondence_address_1' => $validated['correspondence_address_1'] ?? null,
                'correspondence_address_2' => $validated['correspondence_address_2'] ?? null,
                'correspondence_postcode' => $validated['correspondence_postcode'] ?? null,
                'correspondence_district' => $validated['correspondence_district'] ?? null,
                'correspondence_state' => $validated['correspondence_state'] ?? null,
                'correspondence_country' => $validated['correspondence_country'] ?? 'Malaysia',
                'telephone' => $validated['telephone'] ?? null,
                'whatsapp' => $validated['whatsapp'] ?? null,
                'email' => $validated['email'] ?? null,
                'marital_status' => $validated['marital_status'] ?? 'single',
                'emergency_name' => $validated['emergency_name'] ?? null,
                'emergency_telephone' => $validated['emergency_telephone'] ?? null,
                'emergency_relationship' => $validated['emergency_relationship'] ?? null,
                'salary' => $validated['salary'] ?? null,
                'position' => $validated['position'] ?? null,
                'join_date' => $validated['join_date'] ?? null,
                'time_works' => $validated['time_works'] ?? null,
                'status' => $validated['status'] ?? 'active',
            ]);

            // Handle deleted educations
            if (!empty($request->deleted_educations)) {
                $deletedIds = array_filter(explode(',', $request->deleted_educations));
                EmployeeEducation::whereIn('id', $deletedIds)->delete();
            }

            // Update existing educations
            if (!empty($request->existing_educations)) {
                foreach ($request->existing_educations as $id => $eduData) {
                    EmployeeEducation::where('id', $id)->update([
                        'level' => $eduData['level'],
                        'institution' => $eduData['institution'],
                        'field_of_study' => $eduData['field_of_study'] ?? null,
                        'year_start' => $eduData['year_start'] ?? null,
                        'year_end' => $eduData['year_end'] ?? null,
                        'grade' => $eduData['grade'] ?? null,
                    ]);
                }
            }

            // Add new educations
            if (!empty($validated['educations'])) {
                foreach ($validated['educations'] as $edu) {
                    $employee->educations()->create($edu);
                }
            }

            // Save new attachments
            $attachmentTypes = ['front_ic', 'back_ic', 'resume', 'certificate', 'offer_letter'];
            foreach ($attachmentTypes as $type) {
                if ($request->hasFile($type)) {
                    $file = $request->file($type);
                    $path = $file->store('employees/' . $employee->id, 'public');
                    $employee->attachments()->create([
                        'type' => $type,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            // Handle user account
            if ($employee->user) {
                // Update existing user
                $userData = ['role_id' => $validated['role_id'] ?? null];
                if (!empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }
                $employee->user->update($userData);
            } elseif ($request->link_existing_user && $request->existing_user_id) {
                // Link to existing user
                $employee->update(['user_id' => $request->existing_user_id]);
                // Update role if provided
                if ($request->role_id) {
                    User::where('id', $request->existing_user_id)->update(['role_id' => $request->role_id]);
                }
            } elseif ($request->create_account && $request->username && $request->new_password) {
                // Create new user account
                $user = User::create([
                    'name' => $validated['full_name'],
                    'email' => $request->username,
                    'password' => Hash::make($request->new_password),
                    'role_id' => $validated['role_id'] ?? null,
                    'is_active' => true,
                ]);
                $employee->update(['user_id' => $user->id]);
            }

            DB::commit();
            
            // Log employee update
            try {
                ActivityLogService::logUpdate($employee, 'internal_employee', ['full_name' => $employee->full_name], "Updated employee {$employee->full_name}");
            } catch (\Exception $e) {
                \Log::error('Activity logging failed: ' . $e->getMessage());
            }
            
            return redirect()->route('internal.employee.show', $employee)
                ->with('success', 'Employee updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update employee: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Employee $employee)
    {
        // Log employee deletion before deleting
        try {
            ActivityLogService::logDelete($employee, 'internal_employee', "Deleted employee {$employee->full_name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }
        
        // Delete attachments from storage
        foreach ($employee->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }
        
        $employee->delete();

        return redirect()->route('internal.employee.index')
            ->with('success', 'Employee deleted successfully.');
    }

    public function downloadAttachment(EmployeeAttachment $attachment)
    {
        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'File not found');
        }
        
        // Log download activity
        try {
            ActivityLogService::logDownload('internal_employee', "Downloaded employee attachment {$attachment->file_name}", [
                'file_name' => $attachment->file_name,
                'file_type' => $attachment->file_type,
                'employee_id' => $attachment->employee_id,
            ]);
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }
        
        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }
}
