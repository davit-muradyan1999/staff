<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyBalance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'company_balance';

    protected $fillable = [
        'company_id',
        'time',
        'balance',
    ];

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }
}
