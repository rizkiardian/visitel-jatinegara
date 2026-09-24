<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessCustomer extends Model
{
    protected $table = 'business_customer';

    protected $fillable = [
        'id',
        'name',
        'nipnas',
        'status',
        'employee_id',
        'telda_id',
        'service_id',
        'default_pic_name',
        'default_pic_contact',
        'address',
        'segment',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function telda(): BelongsTo
    {
        return $this->belongsTo(Telda::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function visitReports(): HasMany
    {
        return $this->hasMany(VisitReport::class);
    }
}
