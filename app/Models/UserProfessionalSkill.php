<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProfessionalSkill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'professional_skill_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function professionalSkill()
    {
        return $this->belongsTo(ProfessionalSkill::class);
    }
}
