<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $table = 'attendance';

    protected $fillable = [
        'id',
        'employee_id',
        'date',
        'check_in_time',
        'check_out_time',
        'status',
        'late_minutes',
        'day_type',
        'is_mandatory',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'is_mandatory' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
