<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Report extends Model
{
    public const CATEGORIES = [
        'pju_rusak' => 'PJU Rusak',
        'area_gelap' => 'Area Gelap',
        'begal_curas' => 'Begal/Curas',
        'lain_lain' => 'Lain-lain',
    ];

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? 'Lain-lain';
    }

    protected $fillable = [
        'user_id',
        'category',
        'description',
        'latitude',
        'longitude',
        'location_name',
        'status',
        'area_id',
        'tracking_code',
        'incident_at',
    ];
    protected function casts(): array
    {
        return [
            'incident_at' => 'datetime',
        ];
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function images(): HasMany
    {
        return $this->hasMany(ReportImage::class);
    }
    public function updates(): HasMany
    {
        return $this->hasMany(ReportUpdate::class);
    }
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(Report::class, 'duplicate_of_report_id');
    }
    public function assignments(): HasMany
    {
        return $this->hasMany(ReportAssignment::class);
    }

    public function activeAssignment(): HasOne
    {
        return $this->hasOne(ReportAssignment::class)
            ->whereNull('ended_at');
    }
}
