<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'paying_user_id',
        'paid_employee_id',
        'amount',
        'note',
    ];

    public function payingUser()
    {
        return $this->belongsTo(User::class, 'paying_user_id');
    }

    public function paidEmployee()
    {
        return $this->belongsTo(User::class, 'paid_employee_id');
    }

    public function details()
    {
        return $this->hasMany(TransferDetail::class);
    }
}
