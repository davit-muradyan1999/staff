<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkerInfo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'worker_info';

    protected $casts = [
        'first_name'            => 'json',
        'last_name'             => 'json',
        'surname'               => 'json',
        'place_of_birth'        => 'json',
        'series_and_number'     => 'json',
        'issued_by'             => 'json',
        'residence_address'     => 'json',
        'insurance_certificate' => 'json',
        'kpp'                   => 'json',
        'correspondent_account' => 'json',
        'bik'                   => 'json',
        'recipient_bank'        => 'json',
        'recipient'             => 'json',
        'inn'                   => 'json',
        'snils'                 => 'json',
        'medical_book'          => 'json',

    ];

    protected $fillable = [
        'user_id',
        'citizenship_id',
        'first_name',
        'last_name',
        'surname',
        'birthday',
        'place_of_birth',
        'gender_id',
        'series_and_number',
        'issued_by',
        'date_of_issue',
        'validity',
        'residence_address',
        'insurance_certificate',
        'insurance_certificate_file',
        'passport_file_1',
        'passport_file_2',
        'passport_file_3',

        'current_migration_registration',
        'migration_card',
        'disability_group_id',
        'disability_group_file',
        'medical_book',
        'medical_book_file',
        'snils',
        'snils_file',
        'inn',
        'inn_file',
        'passport_translation_file_1',
        'passport_translation_file_2',
        'passport_translation_file_3',
        'passport_translation_file_4',

        'currency_id',
        'account_number',
        'recipient',
        'recipient_bank',
        'bik',
        'correspondent_account',
        'kpp',
        'swift_code',

        'migration_card_file_1',
        'migration_card_file_2',
        'migration_account_file_1',
        'migration_account_file_2',
        'temporary_registration_file_1',
        'temporary_registration_file_2',
        'rvp_file_1',
        'rvp_file_2',
        'vnj_file_1',
        'vnj_file_2',
        'payment_check_file_1',
        'payment_check_file_2',
        'payment_check_file_3',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function citizenship()
    {
        return $this->belongsTo(Citizenship::class);
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class);
    }

    public function disabilityGroup()
    {
        return $this->belongsTo(DisabilityGroup::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
