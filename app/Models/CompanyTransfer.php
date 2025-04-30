<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyTransfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'company_transfers';

    protected $casts = [
        'response' => 'json',
    ];

    protected $fillable = [
        'status',
        'operation_id',
        'response',
    ];
}
