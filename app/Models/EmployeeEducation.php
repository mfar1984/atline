<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeEducation extends Model
{
    use HasFactory;

    protected $table = 'employee_educations';

    protected $fillable = [
        'employee_id', 'level', 'institution', 'field_of_study',
        'year_start', 'year_end', 'grade'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
