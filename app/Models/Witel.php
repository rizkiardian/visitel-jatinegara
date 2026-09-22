<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Witel extends Model
{
    protected $table = 'witel';

    protected $fillable = ['id', 'name'];

    public function teldas(): HasMany
    {
        return $this->hasMany(Telda::class);
    }
}
