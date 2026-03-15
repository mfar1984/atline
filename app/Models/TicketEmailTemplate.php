<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketEmailTemplate extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'description',
        'recipient_type',
        'subject',
        'content',
        'is_active',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get available placeholders for templates
     */
    public static function getPlaceholders(): array
    {
        return [
            '{{name}}' => 'Full name of the recipient',
            '{{first_name}}' => 'First name of the recipient',
            '{{ticket_number}}' => 'Ticket tracking number',
            '{{ticket_subject}}' => 'Ticket subject/title',
            '{{ticket_status}}' => 'Current ticket status',
            '{{ticket_priority}}' => 'Ticket priority level',
            '{{ticket_url}}' => 'Direct link to the ticket',
            '{{num_tickets}}' => 'Number of tickets',
            '{{list_tickets}}' => 'List of support tickets',
            '{{reply_content}}' => 'Content of the reply',
            '{{site_title}}' => 'Website/Company name',
            '{{site_url}}' => 'Website URL',
        ];
    }

    /**
     * Parse template content with actual values
     */
    public function parseContent(array $data): string
    {
        $content = $this->content;
        
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        
        return $content;
    }

    /**
     * Parse template subject with actual values
     */
    public function parseSubject(array $data): string
    {
        $subject = $this->subject;
        
        foreach ($data as $key => $value) {
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
        }
        
        return $subject;
    }

    /**
     * Get recipient type badge color
     */
    public function getRecipientBadgeColorAttribute(): string
    {
        return match($this->recipient_type) {
            'client' => 'bg-blue-100 text-blue-600',
            'staff' => 'bg-green-100 text-green-600',
            'admin' => 'bg-purple-100 text-purple-600',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    /**
     * Get recipient type label
     */
    public function getRecipientLabelAttribute(): string
    {
        return match($this->recipient_type) {
            'client' => 'Client',
            'staff' => 'Staff',
            'admin' => 'Admin',
            default => 'Unknown',
        };
    }
}
