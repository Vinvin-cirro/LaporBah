<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaUpdate extends Model
{
    protected $fillable = [
        'area_id',
        'updated_by',
        'old_status',
        'new_status',
        'action_description',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
