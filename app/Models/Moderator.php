<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Moderator extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'name' => 'json',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function moderatorBranches()
    {
        return $this->belongsToMany(Branch::class, 'moderator_branches', 'moderator_id', 'branche_id');
    }

    public function moderatorPositions()
    {
        return $this->belongsToMany(Position::class, 'moderator_positions', 'moderator_id', 'position_id');
    }
}
