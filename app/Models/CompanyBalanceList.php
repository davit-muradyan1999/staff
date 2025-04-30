<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyBalanceList extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'company_balance_lists';

    protected $fillable = [
        'type',
        'action',
        'operation_id',
        'company_id',
        'employee_id',
        'job_id',
        'job_check_list_id',
        'amount',
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
}
