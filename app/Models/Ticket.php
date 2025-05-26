<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'priority', 'status', 'is_resolved', 'comment', 'assigned_by', 'assigned_to'];


    const STATUS = [
        'Open' => 'Open',
        'Close' => 'Close',
        'Archived' => 'Archived'
    ];

    const PRIORITY = [
        'Low' => 'Low',
        'Medium' => 'Medium',
        'High' => 'High'
    ];

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class);
    }

}
