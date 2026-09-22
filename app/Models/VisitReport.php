<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisitReport extends Model
{
    protected $table = 'visit_report';

    protected $fillable = [
        'id',
        'employee_id',
        'business_customer_id',
        'customer_pic_name',
        'activity_type_id',
        'r_level_id',
        'activity_category_id',
        'estimated_value',
        'activity_description',
        'action_plan',
        'voc',
        'visit_type',
        'visit_date',
        'visit_time',
        'validation_status',
        'validator_id',
        'validation_notes',
        'validated_at',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'validated_at' => 'datetime',
        'estimated_value' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'validator_id');
    }

    public function businessCustomer(): BelongsTo
    {
        return $this->belongsTo(BusinessCustomer::class, 'business_customer_id');
    }

    public function activityType(): BelongsTo
    {
        return $this->belongsTo(ActivityType::class);
    }

    public function rLevel(): BelongsTo
    {
        return $this->belongsTo(RLevel::class);
    }

    public function activityCategory(): BelongsTo
    {
        return $this->belongsTo(ActivityCategory::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'visit_report_service');
    }

    public function locations(): HasMany
    {
        return $this->hasMany(ReportLocation::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ReportPhoto::class);
    }
}
