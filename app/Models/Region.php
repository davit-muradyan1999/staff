<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'name' => 'json'
    ];

    protected $fillable = [
        'district_id',
        'name',
        'is_active',
    ];

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
