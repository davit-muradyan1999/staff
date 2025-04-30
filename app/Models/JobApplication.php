<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'job_id',
        'company_id',
        'branche_id',
        'is_late_added'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function branche()
    {
        return $this->belongsTo(Branch::class, 'branche_id');
    }
}
