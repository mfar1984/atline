<?php

namespace App\Models;

use App\Traits\RecycleBin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory, RecycleBin;

    protected $fillable = [
        'ticket_number',
        'organization_id',
        'client_id',
        'asset_id',
        'created_by',
        'assigned_to',
        'subject',
        'description',
        'priority',
        'priority_id',
        'status',
        'status_id',
        'category',
        'category_id',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function ticketCategory()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function ticketPriority()
    {
        return $this->belongsTo(TicketPriority::class, 'priority_id');
    }

    public function ticketStatus()
    {
        return $this->belongsTo(TicketStatus::class, 'status_id');
    }

    /**
     * Get all assignees for the ticket (many-to-many)
     */
    public function assignees()
    {
        return $this->belongsToMany(User::class, 'ticket_assignees')
            ->withTimestamps();
    }

    /**
     * Check if a user is assigned to this ticket
     */
    public function isAssignedTo($userId): bool
    {
        return $this->assignees()->where('user_id', $userId)->exists() 
            || $this->assigned_to === $userId;
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class)->orderBy('created_at', 'asc');
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class)->whereNull('reply_id');
    }

    public static function generateTicketNumber(): string
    {
        $prefix = 'TKT';
        $date = now()->format('Ymd');
        $lastTicket = self::whereDate('created_at', today())->orderBy('id', 'desc')->first();
        $sequence = $lastTicket ? (int)substr($lastTicket->ticket_number, -4) + 1 : 1;
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'bg-gray-100 text-gray-600',
            'medium' => 'bg-blue-100 text-blue-600',
            'high' => 'bg-orange-100 text-orange-600',
            'urgent' => 'bg-red-100 text-red-600',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open' => 'bg-blue-100 text-blue-600',
            'in_progress' => 'bg-yellow-100 text-yellow-600',
            'pending' => 'bg-orange-100 text-orange-600',
            'resolved' => 'bg-green-100 text-green-600',
            'closed' => 'bg-gray-100 text-gray-600',
            default => 'bg-gray-100 text-gray-600',
        };
    }
}
