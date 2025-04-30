<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserExperience extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'organization' => 'json',
        'position' => 'json',
    ];

    protected $fillable = [
        'user_id',
        'organization',
        'position',
        'started_at',
        'finished_at',
        'is_active_work',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
