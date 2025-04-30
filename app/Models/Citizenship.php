<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Citizenship extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'name' => 'json'
    ];

    protected $fillable = [
        'name',
        'show_in_profile',
        'is_active',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
