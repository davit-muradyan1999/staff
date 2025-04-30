<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobSpecialBranche extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'branche_id',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function branche()
    {
        return $this->belongsTo(Branch::class);
    }
}
