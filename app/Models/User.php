<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role',
        'type',
        'name',
        'email',
        'parent_user_id',
        'phone',
        'company_name',
        'company_phone',
        'address_of_residence',
        'registration_address',
        'gender_id',
        'citizenship_id',
        'birthday',
        'password',
        'profile_photo_path',
        'company_photo_path',
        'verify_token',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at'  => 'date:Y-m-d H:i:s',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function positions()
    {
        return $this->belongsToMany(Position::class, 'user_positions');
    }

    public function fieldOfActivities()
    {
        return $this->belongsToMany(FieldOfActivity::class, 'user_field_of_activities');
    }

    public function educations()
    {
        return $this->belongsToMany(Education::class, 'user_educations');
    }

    public function professionalSkills()
    {
        return $this->belongsToMany(ProfessionalSkill::class, 'user_professional_skills');
    }

    public function personalSkills()
    {
        return $this->belongsToMany(PersonalSkill::class, 'user_personal_skills');
    }

    public function languageSkills()
    {
        return $this->belongsToMany(LanguageSkill::class, 'user_language_skills');
    }

    public function driverLicenses()
    {
        return $this->belongsToMany(DriverLicense::class, 'user_driver_licenses');
    }

    public function hobbies()
    {
        return $this->belongsToMany(Hobby::class, 'user_hobbies');
    }

    public function companyInfo()
    {
        return $this->hasOne(CompanyInfo::class);
    }

    public function workerInfo()
    {
        return $this->hasOne(WorkerInfo::class);
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'user_branches', 'user_id', 'branche_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions', 'user_id', 'permission_id');
    }

    public function userSupports()
    {
        return $this->belongsToMany(User::class, 'user_supports', 'user_id', 'support_user_id');
    }

    // public function hasPermission($id)
    // {
    //     return $this->permissions->contains($id);
    // }

    public function userExperiences()
    {
        return $this->hasMany(UserExperience::class);
    }

    public function userAdvantageEmployees()
    {
        return $this->belongsToMany(AdvantageEmploye::class, 'user_advantage_employees', 'user_id', 'advantage_employee_id');
    }

    public function statuses()
    {
        return $this->hasMany(UserStatus::class);
    }

    public function statusLatest()
    {
        return $this->hasOne(UserStatus::class)->latestOfMany();
    }

    public function parentUser()
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'user_id');
    }

    public function blackListEmployee()
    {
        return $this->hasMany(BlackList::class, 'employee_id');
    }

    public function employeeBalance()
    {
        return $this->hasOne(EmployeeBalance::class, 'employee_user_id');
    }

    public function employeeBalanceList()
    {
        return $this->hasmany(EmployeeBalanceList::class, 'employee_id');
    }

    public function employeeBalanceListOldest()
    {
        return $this->hasOne(EmployeeBalanceList::class, 'employee_id');
    }

    public function listUnpaidOldest()
    {
        return $this->hasOne(EmployeeBalanceList::class, 'employee_id')->where('is_paid', 0)->orderBy('id', 'asc');
    }

    public function listUnpaidLatest()
    {
        return $this->hasOne(EmployeeBalanceList::class, 'employee_id')->where('is_paid', 0)->orderBy('id', 'desc');
    }

    public function listPaidOldest()
    {
        return $this->hasOne(EmployeeBalanceList::class, 'employee_id')->where('is_paid', 1)->oldestOfMany();
    }

    public function listPaidLatest()
    {
        return $this->hasOne(EmployeeBalanceList::class, 'employee_id')->where('is_paid', 1)->latestOfMany();
    }

    public function login_histories() {
        return $this->hasMany('App\Models\UserLoginHistory');
    }

    public function last_login_history($type = null) {
        return $this->hasOne('App\Models\UserLoginHistory')->when($type, function ($q) use($type) {
            return $q->where('type', $type);
        })->where('status', 1)->orderBy('id', 'desc');
    }
}
