<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class OfficerTaskUpdate extends Model {
    protected $fillable = ['report_assignment_id','user_id','event','note','metadata'];
    protected function casts(): array { return ['metadata'=>'array']; }
    public function assignment(): BelongsTo { return $this->belongsTo(ReportAssignment::class,'report_assignment_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
