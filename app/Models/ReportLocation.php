<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportLocation extends Model
{
    protected $table = 'report_location';

    protected $fillable = [
        'id',
        'visit_report_id',
        'latitude',
        'longitude',
        'accuracy_meters',
        'captured_at',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
    ];

    public function visitReport(): BelongsTo
    {
        return $this->belongsTo(VisitReport::class);
    }
}
