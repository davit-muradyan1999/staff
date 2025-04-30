<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'name' => 'json'
    ];

    protected $fillable = [
        'territory_type_id',
        'region_id',
        'name',
        'is_active',
    ];

    public function territory_type()
    {
        return $this->belongsTo(TerritoryType::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
