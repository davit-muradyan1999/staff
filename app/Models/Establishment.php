<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Establishment extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'name' => 'json',
        'obligation' => 'json',
        'requirement' => 'json',
    ];

    protected $fillable = [
        'user_id',
        'admin_user_id',
        'company_id',
        'status',
        'admin_status',
        'gender',
        'name',
        'obligation',
        'requirement',
        'absence_disability',
        'salary',
        'tax',
        'commission',
        'img',
        'admin_status_updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function adminUser()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function driverLicenses()
    {
        return $this->belongsToMany(Branch::class, 'establishment_driver_licenses', 'establishment_id', 'driver_license_id');
    }

    public function meanOfTransports()
    {
        return $this->belongsToMany(Branch::class, 'establishment_mean_of_transports', 'establishment_id', 'mean_of_transport_id');
    }

    public function driverLicenseLists()
    {
        return $this->hasMany(EstablishmentDriverLicense::class);
    }

    public function meanOfTransportLists()
    {
        return $this->hasMany(EstablishmentMeanOfTransport::class);
    }

    public function statuses()
    {
        return $this->hasMany(EstablishmentStatus::class);
    }

    public function positions()
    {
        return $this->belongsToMany(Position::class, 'establishment_positions');
    }
}
