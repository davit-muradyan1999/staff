<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobFavorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'employee_user_id',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function employeeUser()
    {
        return $this->belongsTo(User::class, 'employee_user_id');
    }
}
