<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstablishmentStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'status',
        'establishment_id',
        'created_user_id',
        'updated_user_id',
        'description',
    ];

    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }

    public function createdUser()
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }

    public function updatedUser()
    {
        return $this->belongsTo(User::class, 'updated_user_id');
    }
}
