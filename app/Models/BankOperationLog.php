<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankOperationLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'status',
        'type',
        'user_id',
        'sended_correlation_id',
        'received_correlation_id',
        'request_log',
        'response_log',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
