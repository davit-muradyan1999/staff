<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobCheckListShiftReason extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'job_check_list_id',
        'shift_reason_id',
        'description',
    ];

    public function jobCheckList()
    {
        return $this->belongsTo(JobCheckList::class);
    }

    public function shiftReason()
    {
        return $this->belongsTo(ShiftReason::class);
    }
}
