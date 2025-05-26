<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Label extends Model
{
    use HasFactory;
    protected $fillable = ['name' , 'is_active'];

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active' , true);
    }
}
