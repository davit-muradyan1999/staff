<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'title' => 'json',
        'city' => 'json',
        'phone' => 'json',
        'address' => 'json',
    ];

    protected $fillable = [
        'user_id',
        'title',
        'city',
        'phone',
        'address',
        'lat',
        'lng',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
