<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstablishmentDriverLicense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'establishment_id',
        'driver_license_id',
    ];

    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }

    public function driverLicense()
    {
        return $this->belongsTo(DriverLicense::class);
    }
}
