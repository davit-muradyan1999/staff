<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeBalanceListTransfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_balance_list_id',
        'status',
        'payment_registry_id',
        'request_log',
        'response_log',

        'is_csrp_success',
        'csrp_at',
        'csrp_response_log',
        'csrp_request_log',

        'sprsr_response_log',
        'sprsr_at',

        'is_sprs_signed',
        'sprs_at',
        'sprs_response_log',
        'sprs_request_log',

        'sgprcr_status',
        'sgprcr_at',
        'sgprcr_response_log',
    ];

    public function employeeBalanceList()
    {
        return $this->belongsTo(EmployeeBalanceList::class, 'employee_balance_list_id');
    }
}
