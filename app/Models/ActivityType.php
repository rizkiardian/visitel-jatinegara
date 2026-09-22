<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityType extends Model
{
    protected $table = 'activity_type';

    protected $fillable = ['id', 'name'];

    public function visitReports(): HasMany
    {
        return $this->hasMany(VisitReport::class);
    }
}
