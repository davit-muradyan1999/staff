<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Job extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'job_type_id',
        'company_id',
        'user_id',
        'establishment_id',
        'branche_id',
        'currency_id',
        'bonus',
        'employee_qnt',
        'is_need_internship',
        'is_paid_lunch',
        'is_active',
    ];

    public function jobType()
    {
        return $this->belongsTo(JobType::class);
    }

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }

    public function branche()
    {
        return $this->belongsTo(Branch::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function specialEmployees()
    {
        return $this->hasMany(JobSpecialEmployee::class);
    }

    public function specialBranches()
    {
        return $this->hasMany(JobSpecialBranche::class);
    }

    public function advantageEmployees()
    {
        return $this->hasMany(JobAdvantageEmployee::class);
    }

    public function graphics()
    {
        return $this->hasMany(JobTimeGraphic::class);
    }

    public function firstGraphic()
    {
        return $this->hasOne(JobTimeGraphic::class)->oldestOfMany();
    }

    public function lastGraphic()
    {
        return $this->hasOne(JobTimeGraphic::class)->latestOfMany();
    }

    public function favorite()
    {
        return $this->hasOne(JobFavorite::class);
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function companySupports()
    {
        return $this->hasMany(UserSupport::class, 'user_id', 'company_id');
    }

    protected static function booted()
    {
        static::addGlobalScope('delete', function (Builder $builder) {
            if(Auth::user()->role == 'COMPANY') {
                $builder->where('jobs.company_id', Auth::user()->id);
            }
        });
    }
}
