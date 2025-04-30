<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserDriverLicense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'driver_license_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function driverLicense()
    {
        return $this->belongsTo(DriverLicense::class);
    }
}
