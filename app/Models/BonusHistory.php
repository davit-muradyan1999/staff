<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BonusHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'status',
        'created_user_id',
        'company_id',
        'employee_id',
        'job_id',
        'amount',
        'reason',
    ];

    public function createdUser()
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
