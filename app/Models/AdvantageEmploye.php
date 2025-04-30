<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdvantageEmploye extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'advantage_employees';

    protected $casts = [
        'name' => 'json'
    ];

    protected $fillable = [
        'name',
        'is_active',
    ];

    public function userAdvantageEmployees()
    {
        return $this->hasMany(UserAdvantageEmployee::class, 'advantage_employee_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
