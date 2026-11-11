<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    protected $fillable = [
        'name',
        'center_latitude',
        'center_longitude',
        'status',
        'last_reported_at',
    ];

    protected function casts(): array
    {
        return [
            'last_reported_at' => 'datetime',
        ];
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
    public function updates(): HasMany
    {
        return $this->hasMany(AreaUpdate::class);
    }
}
