<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyInfoAdvantage extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_info_id',
        'advantage_employee_id',
    ];

    public function companyInfo()
    {
        return $this->belongsTo(CompanyInfo::class, 'company_info_id');
    }

    public function sdvantageEmploye()
    {
        return $this->belongsTo(AdvantageEmploye::class, 'advantage_employee_id');
    }
}
