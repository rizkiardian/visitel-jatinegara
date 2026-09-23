<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    protected $table = 'employee';

    protected $fillable = [
        'id',
        'name',
        'email',
        'phone',
        'role_id',
        'telda_id',
        'is_active',
        'nip',
        'supervisor_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function telda(): BelongsTo
    {
        return $this->belongsTo(Telda::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'supervisor_id');
    }

    public function visitReports(): HasMany
    {
        return $this->hasMany(VisitReport::class, 'employee_id');
    }

    public function validatedReports(): HasMany
    {
        return $this->hasMany(VisitReport::class, 'validator_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function businessCustomers(): HasMany
    {
        return $this->hasMany(BusinessCustomer::class, 'employee_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
