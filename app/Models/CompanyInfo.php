<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyInfo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'company_info';

    protected $casts = [
        'who_are_we'            => 'json',
        'location'              => 'json',
        'inn'                   => 'json',
        'ogrn'                  => 'json',
        'kpp'                   => 'json',
        'address'               => 'json',
        'okpo'                  => 'json',
        'okved'                 => 'json',
        'okopf'                 => 'json',
        'okfs'                  => 'json',
        'bank_name'             => 'json',
        'bank_bip'              => 'json',
    ];

    protected $fillable = [
        'user_id',
        'industry_id',
        'quantity_employee_id',
        'company_type_id',
        'city_id',
        'contact_city_id',
        'email',
        'phone',
        'company_site_url',
        'foundation_date',
        'who_are_we',
        'location',
        'organization_card',
        'inn',
        'ogrn',
        'kpp',
        'address',
        'okpo',
        'okved',
        'okopf',
        'okfs',
        'bank_name',
        'checking_account',
        'correspondent_account',
        'bank_bip',
        'charter',
        'tax_certificate',
        'extract_egrul',
        'passport_gen_director',
        'certificate_of_no_debt',
        'tax_declarations_last_year',
        'tax_declarations_last_period',
        'lat',
        'lng',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function industry()
    {
        return $this->belongsTo(Industry::class);
    }

    public function quantityEmployee()
    {
        return $this->belongsTo(QuantityEmploye::class);
    }

    public function companyType()
    {
        return $this->belongsTo(CompanyType::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function contactCity()
    {
        return $this->belongsTo(City::class, 'contact_city_id');
    }
}
