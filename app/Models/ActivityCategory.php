<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityCategory extends Model
{
    protected $table = 'activity_category';

    protected $fillable = ['id', 'name'];

    public function visitReports(): HasMany
    {
        return $this->hasMany(VisitReport::class);
    }
}
