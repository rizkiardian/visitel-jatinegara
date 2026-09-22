<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Telda extends Model
{
    protected $table = 'telda';

    protected $fillable = ['id', 'name', 'witel_id'];

    public function witel(): BelongsTo
    {
        return $this->belongsTo(Witel::class);
    }

    public function businessCustomers(): HasMany
    {
        return $this->hasMany(BusinessCustomer::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
