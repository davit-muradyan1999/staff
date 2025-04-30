<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;


class UserLoginHistory extends Model
{
    protected $fillable = array(
        'type',
        'social_type',
        'user_agent',
        'remote_addr',
        'username',
        'user_id',
        'status',
    );

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }
}
