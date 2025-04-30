<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobCheckList extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_user_id',
        'job_id',
        'company_id',
        'branche_id',
        'job_time_graphic_id',
        'salary',
        'tax',
        'commission',
        'status',
        'lunch',
        'is_paid_lunch',
        'started_at',
        'finished_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employeeUser()
    {
        return $this->belongsTo(User::class, 'employee_user_id');
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
        return $this->belongsTo(Branch::class);
    }

    public function jobTimeGraphic()
    {
        return $this->belongsTo(JobTimeGraphic::class);
    }

    public function employeeBalanceList()
    {
        return $this->hasMany(EmployeeBalanceList::class, 'employee_id', 'employee_user_id');
    }
}
