<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeBalanceList extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employee_balance_lists';

    protected $fillable = [
        'status',
        'type',
        'action',
        'company_id',
        'employee_id',
        'job_id',
        'job_check_list_id',
        'amount',
        'is_paid',
        'paid_created_at'
    ];

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

    public function jobCheckList()
    {
        return $this->belongsTo(JobCheckList::class);
    }

    public function shiftOldest()
    {
        return $this->hasOne(JobCheckList::class, 'id', 'job_check_list_id')->orderBy('started_at', 'asc');
    }

    public function shiftLatest()
    {
        return $this->hasOne(JobCheckList::class, 'id', 'job_check_list_id')->orderBy('finished_at', 'desc');
    }
}
