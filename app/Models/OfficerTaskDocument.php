<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class OfficerTaskDocument extends Model {
    protected $fillable = ['report_assignment_id','user_id','kind','file_path','caption'];
    public function assignment(): BelongsTo { return $this->belongsTo(ReportAssignment::class,'report_assignment_id'); }
}
