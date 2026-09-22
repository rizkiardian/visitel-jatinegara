<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RLevel extends Model
{
    protected $table = 'r_level';

    protected $fillable = ['id', 'name', 'sort_order'];

    public function visitReports(): HasMany
    {
        return $this->hasMany(VisitReport::class);
    }
}
