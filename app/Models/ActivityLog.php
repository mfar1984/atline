<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'client_id',
        'employee_id',
        'action',
        'module',
        'subject_type',
        'subject_id',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * Get the user that performed the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the client that performed the activity.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the employee/staff that performed the activity.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the subject of the activity.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get user type (client, staff, or system).
     */
    public function getUserTypeAttribute(): string
    {
        if ($this->client_id) return 'client';
        if ($this->employee_id) return 'staff';
        return 'system';
    }

    /**
     * Get action badge color.
     */
    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'login' => 'bg-green-100 text-green-600',
            'logout' => 'bg-gray-100 text-gray-600',
            'create' => 'bg-blue-100 text-blue-600',
            'update' => 'bg-yellow-100 text-yellow-600',
            'delete' => 'bg-red-100 text-red-600',
            'view' => 'bg-purple-100 text-purple-600',
            'export' => 'bg-indigo-100 text-indigo-600',
            'assign' => 'bg-cyan-100 text-cyan-600',
            'status_change' => 'bg-orange-100 text-orange-600',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    /**
     * Get action icon.
     */
    public function getActionIconAttribute(): string
    {
        return match($this->action) {
            'login' => 'login',
            'logout' => 'logout',
            'create' => 'add_circle',
            'update' => 'edit',
            'delete' => 'delete',
            'view' => 'visibility',
            'export' => 'download',
            'assign' => 'person_add',
            'status_change' => 'sync',
            default => 'info',
        };
    }
}
