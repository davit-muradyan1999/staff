<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserBranche extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_branches';

    protected $fillable = [
        'user_id',
        'branche_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branche()
    {
        return $this->belongsTo(Branch::class);
    }
}
