<?php

namespace App\Models;

use App\Traits\RecycleBin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory, RecycleBin;

    protected $fillable = [
        'user_id', 'full_name', 'ic_number', 'birthday',
        'current_address_1', 'current_address_2', 'current_postcode',
        'current_district', 'current_state', 'current_country',
        'correspondence_address_1', 'correspondence_address_2', 'correspondence_postcode',
        'correspondence_district', 'correspondence_state', 'correspondence_country',
        'telephone', 'whatsapp', 'email', 'marital_status',
        'emergency_name', 'emergency_telephone', 'emergency_relationship',
        'salary', 'position', 'join_date', 'time_works', 'status'
    ];

    protected $casts = [
        'birthday' => 'date',
        'join_date' => 'date',
        'salary' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function educations()
    {
        return $this->hasMany(EmployeeEducation::class);
    }

    public function attachments()
    {
        return $this->hasMany(EmployeeAttachment::class);
    }

    public function getYearsOfServiceAttribute()
    {
        if (!$this->join_date) return 0;
        return (int) $this->join_date->diffInYears(now());
    }

    public function getServiceDurationAttribute()
    {
        if (!$this->join_date) return '0 Day';
        
        $now = now();
        $joinDate = $this->join_date->copy();
        
        // Calculate years
        $years = (int) $joinDate->diffInYears($now);
        
        // Calculate months after removing years
        $afterYears = $joinDate->copy()->addYears($years);
        $months = (int) $afterYears->diffInMonths($now);
        
        // Calculate days after removing years and months
        $afterMonths = $afterYears->copy()->addMonths($months);
        $days = (int) $afterMonths->diffInDays($now);
        
        $parts = [];
        if ($years > 0) {
            $parts[] = $years . ' ' . ($years == 1 ? 'Year' : 'Years');
        }
        if ($months > 0) {
            $parts[] = $months . ' ' . ($months == 1 ? 'Month' : 'Months');
        }
        if ($days > 0) {
            $parts[] = $days . ' ' . ($days == 1 ? 'Day' : 'Days');
        }
        
        // If empty (exactly on anniversary), show "0 Day"
        if (empty($parts)) {
            return '0 Day';
        }
        
        return implode(' ', $parts);
    }

    public function getAgeAttribute()
    {
        if (!$this->birthday) return null;
        return $this->birthday->age;
    }
}
