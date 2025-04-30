<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobTimeGraphic extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'job_id',
        'lunch',
        'is_late_added',
        'started_at',
        'finished_at',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
