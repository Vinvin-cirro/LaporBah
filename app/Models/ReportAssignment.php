<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportAssignment extends Model
{
    protected $fillable = [
        'report_id',
        'officer_id',
        'team_id',
        'assignment_type',
        'assigned_by',
        'institution_name',
        'team_name',
        'priority',
        'status',
        'accepted_at',
        'started_at',
        'completion_requested_at',
        'completed_at',
        'assigned_at',
        'due_at',
        'ended_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'due_at' => 'datetime',
            'ended_at' => 'datetime',
            'accepted_at' => 'datetime',
            'started_at' => 'datetime',
            'completion_requested_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    public function updates() { return $this->hasMany(OfficerTaskUpdate::class)->latest(); }

    public function documents() { return $this->hasMany(OfficerTaskDocument::class)->latest(); }

    public function patrolLogs() { return $this->hasMany(OfficerPatrolLog::class)->with('images')->latest('patrol_date'); }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
