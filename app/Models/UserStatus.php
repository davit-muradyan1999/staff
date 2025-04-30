<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class UserStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        // 'created_at'  => 'datetime:Y-m-d H:i:s',
    ];

    protected $fillable = [
        'status',
        'user_id',
        'created_user_id',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdUser()
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }

    // public function getCreatedAtAttribute($date)
    // {

        // return Carbon::createFromFormat('Y-m-d H:i:s', $date)
        // ->timezone('Asia/Yerevan')
        // ->toDateTimeString() ;

        // $date = Carbon::parse($date)->format('Y-m-d H:i:s');
        // return $date;
    // }
}
