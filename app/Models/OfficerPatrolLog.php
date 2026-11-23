<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class OfficerPatrolLog extends Model {
    protected $fillable = ['report_assignment_id','user_id','patrol_date','started_at','ended_at','location','monitoring_result','findings','notes'];
    protected function casts(): array { return ['patrol_date'=>'date']; }
    public function assignment(): BelongsTo { return $this->belongsTo(ReportAssignment::class,'report_assignment_id'); }
    public function images(): HasMany { return $this->hasMany(OfficerPatrolImage::class,'patrol_log_id'); }
}
