<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityType extends Model
{
    protected $table = 'activity_type';

    protected $fillable = ['id', 'name', 'activity_category_id'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ActivityCategory::class, 'activity_category_id');
    }

    public function visitReports(): HasMany
    {
        return $this->hasMany(VisitReport::class);
    }
}
