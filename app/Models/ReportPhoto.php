<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportPhoto extends Model
{
    protected $table = 'report_photo';

    protected $fillable = [
        'id',
        'visit_report_id',
        'photo_type',
        'file_url',
        'uploaded_at',
        'latitude',
        'longitude',
        'file_size',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function visitReport(): BelongsTo
    {
        return $this->belongsTo(VisitReport::class);
    }
}
