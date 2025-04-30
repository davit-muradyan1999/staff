<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSupport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'support_user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supportUser()
    {
        return $this->belongsTo(User::class, 'support_user_id');
    }
}
