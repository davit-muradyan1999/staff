<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Information extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'informations';

    protected $casts = [
        'title' => 'json',
        'name' => 'json',
        'description' => 'json',
        'info' => 'json',
    ];

    protected $fillable = [
        'page',
        'title',
        'name',
        'description',
        'info',
        'img',
        'is_active',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
