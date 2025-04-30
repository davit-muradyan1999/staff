<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeBalance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employee_balance';

    protected $fillable = [
        'employee_user_id',
        'time',
        'balance',
    ];

    public function employeeUser()
    {
        return $this->belongsTo(User::class, 'employee_user_id');
    }
}
