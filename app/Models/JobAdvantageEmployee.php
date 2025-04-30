<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobAdvantageEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'advantage_employee_id',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function advantageEmployee()
    {
        return $this->belongsTo(AdvantageEmploye::class);
    }
}
