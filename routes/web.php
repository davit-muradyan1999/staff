<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Home\HomeController,
    App\Http\Controllers\CommonController;
use App\Http\Controllers\Personal\DashboardController as PersonalDashboardController,
    App\Http\Controllers\Personal\ProfileController as PersonalProfileController,
    App\Http\Controllers\Personal\ActiveWorkController as PersonalActiveWorkController,
    App\Http\Controllers\Personal\JobController as PersonalJobController,
    App\Http\Controllers\Personal\InformationController as PersonalInformationController,
    App\Http\Controllers\Personal\FinanceController as PersonalFinanceController,
    App\Http\Controllers\Personal\CheckListController as PersonalCheckListController;
use App\Http\Controllers\Company\ProfileController as CompanyProfileController,
    App\Http\Controllers\Company\BranchController as CompanyBranchController,
    App\Http\Controllers\Company\ModeratorController as CompanyModeratorController,
    App\Http\Controllers\Company\EmployeeController as CompanyEmployeeController,
    App\Http\Controllers\Company\JobController as CompanyJobController,
    App\Http\Controllers\Company\EstablishmentController as CompanyEstablishmentController,
    App\Http\Controllers\Company\CheckListController as CompanyCheckListController,
    App\Http\Controllers\Company\FinanceController as CompanyFinanceController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController,
    App\Http\Controllers\Admin\PositionController as AdminPositionController,
    App\Http\Controllers\Admin\UserLoginHistoryController as AdminUserLoginHistoryController,
    App\Http\Controllers\Admin\EducationController as AdminEducationController,
    App\Http\Controllers\Admin\ProfessionalSkillController as AdminProfessionalSkillController,
    App\Http\Controllers\Admin\PersonalSkillController as AdminPersonalSkillController,
    App\Http\Controllers\Admin\LanguageSkillController as AdminLanguageSkillController,
    App\Http\Controllers\Admin\FieldOfActivityController as AdminFieldOfActivityController,
    App\Http\Controllers\Admin\DriverLicenseController as AdminDriverLicenseController,
    App\Http\Controllers\Admin\HobbyController as AdminHobbyController,
    App\Http\Controllers\Admin\GenderController as AdminGenderController,
    App\Http\Controllers\Admin\CitizenshipController as AdminCitizenshipController,
    App\Http\Controllers\Admin\IndustryController as AdminIndustryController,
    App\Http\Controllers\Admin\CompanyTypeController as AdminCompanyTypeController,
    App\Http\Controllers\Admin\QuantityEmployeController as AdminQuantityEmployeController,
    App\Http\Controllers\Admin\CountryController as AdminCountryController,
    App\Http\Controllers\Admin\DistrictController as AdminDistrictController,
    App\Http\Controllers\Admin\RegionController as AdminRegionController,
    App\Http\Controllers\Admin\CityController as AdminCityController,
    App\Http\Controllers\Admin\TerritoryTypeController as AdminTerritoryTypeController,
    App\Http\Controllers\Admin\AdvantageEmployeController as AdminAdvantageEmployeController,
    App\Http\Controllers\Admin\PermissionController as AdminPermissionController,
    App\Http\Controllers\Admin\InformationController as AdminInformationController,
    App\Http\Controllers\Admin\PartnerController as AdminPartnerController,
    App\Http\Controllers\Admin\UserController as AdminUserController,
    App\Http\Controllers\Admin\MeanOfTransportController as AdminMeanOfTransportController,
    App\Http\Controllers\Admin\CurrencyController as AdminCurrencyController,
    App\Http\Controllers\Admin\JobController as AdminJobController,
    App\Http\Controllers\Admin\EstablishmentController as AdminEstablishmentController,
    App\Http\Controllers\Admin\ShiftReasonController as AdminShiftReasonController,
    App\Http\Controllers\Admin\CheckListController as AdminCheckListController,
    App\Http\Controllers\Admin\BlackListController as AdminBlackListController,
    App\Http\Controllers\Admin\EmployeeBalanceController as AdminEmployeeBalanceController,
    App\Http\Controllers\Admin\CompanyBalanceController as AdminCompanyBalanceController,
    App\Http\Controllers\Admin\DisabilityGroupController as AdminDisabilityGroupController,
    App\Http\Controllers\Admin\TBankController as AdminTBankController,
    App\Http\Controllers\Admin\SalaryProjectController as AdminSalaryProjectController;

Route::get('/', [HomeController::class, 'home'])->name('home')->middleware('guest');

Route::group(['middleware' => ['web']], function () {
    Route::get('/verify/email/{token}', [CommonController::class, 'verifyEmailToken'])->name('verify_email_token');

    Route::get('/social/yandex_redirect', [SocialAuthController::class, 'yandexRedirect'])->name('yandex_redirect');
    Route::get('/social/yandex_callback', [SocialAuthController::class, 'yandexCallback'])->name('yandex_callback');

    Route::get('/social/mailru_redirect', [SocialAuthController::class, 'mailruRedirect'])->name('mailru_redirect');
    Route::get('/social/mailru_callback', [SocialAuthController::class, 'mailruCallback'])->name('mailru_callback');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session')])->group(function () {

    Route::get('/dashboard', [PersonalDashboardController::class, 'dashboard'])->name('dashboard');

    Route::middleware(['check_role:WORKER,COMPANY,ADMIN,MODERATOR,ADMINISTRATOR'])->group(function () {
        Route::get('/profile',  [PersonalProfileController::class, 'profile'])->name('profile');
        Route::get('/company_profile', [CompanyProfileController::class, 'companyProfile'])->name('company_profile');
    });

    Route::middleware(['check_role:COMPANY,ADMIN,MODERATOR,ADMINISTRATOR'])->group(function () {
        Route::get('/company_jobs/get_worker_lists/{id}', [CompanyJobController::class, 'getWorkerLists'])->name('company_jobs_get_worker_lists');
        Route::get('/company_jobs/get_job_type/{id}', [CompanyJobController::class, 'getJobType'])->name('company_jobs_get_job_type');
        Route::get('/advantage_employees/company/{id}', [AdminAdvantageEmployeController::class, 'getAdvantageEmployeesByCompanyId'])->name('get_advantage_employees_by_company_id');

        Route::middleware(['check_role:WORKER,ADMIN,ADMINISTRATOR'])->group(function () {
            Route::get('/branches/company/{id}', [CompanyBranchController::class, 'getBranchesByCompanyId'])->name('get_branches_by_company_id');
            Route::get('/establishments/company/{id}', [CompanyEstablishmentController::class, 'getEstablishmentsByCompanyId'])->name('get_establishments_by_company_id');
        });

        Route::controller(CompanyCheckListController::class)->group(function () {
            Route::post('/add_company_job_graphic', 'addCompanyJobGraphic')->name('add_company_job_graphic');
        });


        Route::controller(CompanyJobController::class)->group(function () {
            Route::get('/company_jobs/get/{id}', 'companyJobsGetById')->name('company_jobs_get_by_id');
            Route::get('/company_jobs', 'companyJobs')->name('company_jobs');
            Route::get('/company_jobs/add/{id?}', 'companyJobsAdd')->name('company_jobs_add');
            Route::get('/company_jobs/show_list/{id}', 'showList')->name('company_jobs_show_list');
            Route::post('/add_company_job', 'addCompanyJob')->name('add_company_job');
            Route::post('/update_company_job', 'updateCompanyJob')->name('update_company_job');
            Route::post('/delete_company_job', 'deleteCompanyJob')->name('delete_company_job');

            Route::post('/update_company_job_graphic', 'updateCompanyJobGraphic')->name('update_company_job_graphic');
            Route::post('/delete_company_job_graphic', 'deleteCompanyJobGraphic')->name('delete_company_job_graphic');

            Route::get('/company_jobs/shift/get', 'getAddShiftJobs')->name('get_add_shift_jobs');
        });

        Route::controller(CompanyEstablishmentController::class)->group(function () {
            Route::get('/establishment', 'establishment')->name('establishment');
            Route::get('/establishment/{id}', [CompanyEstablishmentController::class, 'getEstablishmentById'])->name('get_establishment_by_id');
            Route::post('/add_establishment', 'addEstablishment')->name('add_establishment');
            Route::post('/update_establishment', 'updateEstablishment')->name('update_establishment');
            Route::post('/delete_establishment', 'deleteEstablishment')->name('delete_establishment');
        });

        Route::controller(CompanyEmployeeController::class)->group(function () {
            Route::get('/employees', 'employees')->name('employees');
            Route::get('/get_company_employees', 'getCompanyEmployees')->name('get_company_employees');
            Route::post('/set_black_list', 'setBlackList')->name('set_black_list');
            Route::get('/get_employee_black_list_statuses', 'getEmployeeBlackListStatuses')->name('get_employee_black_list_statuses');
            Route::get('/get_employee_history', 'getEmployeeHistory')->name('get_employee_history');

        });

        Route::controller(CompanyBranchController::class)->group(function () {
            Route::get('/my_branches', 'myBranches')->name('my_branches');
            Route::post('/add_branch', 'addBranch')->name('add_branch');
            Route::post('/update_branch', 'updateBranch')->name('update_branch');
            Route::post('/delete_branch', 'deleteBranch')->name('delete_branch');
            Route::get('/my_branches/get_moderators', 'getModeratorsByBranche')->name('get_moderators_by_branche');
        });

        Route::controller(CompanyModeratorController::class)->group(function () {
            Route::get('/moderators', 'moderators')->name('moderators');
            Route::post('/add_moderator', 'addModerator')->name('add_moderator');
            Route::post('/update_moderator', 'updateModerator')->name('update_moderator');
            Route::post('/delete_moderator', 'deleteModerator')->name('delete_moderator');
        });

        Route::controller(CompanyCheckListController::class)->group(function () {
            Route::get('/check_lists', 'checkLists')->name('check_lists');
            Route::get('/check_list_bonuses', 'checkListBonuses')->name('check_list_bonuses');
            Route::post('/set_check_list_status', 'setCheckListStatus')->name('set_check_list_status');
        });

        Route::controller(CompanyFinanceController::class)->group(function () {
            Route::get('/balance_info', 'balanceInfo')->name('balance_info');
        });

        Route::controller(CompanyProfileController::class)->group(function () {
            Route::post('/set_company_user_info', 'setCompanyUserInfo');
            Route::post('/set_who_are_we', 'setWhoAreWe');
            Route::post('/set_industry', 'setIndustry');
            Route::post('/set_contact', 'setContact');
            Route::post('/set_advantage', 'setAdvantage');
            Route::post('/set_company_info', 'setCompanyInfo');
        });
    });

    Route::middleware(['check_role:WORKER,ADMIN,ADMINISTRATOR'])->group(function () {
        Route::get('/active_works', [PersonalActiveWorkController::class, 'activeWorks'])->name('active_works');

        Route::controller(PersonalFinanceController::class)->group(function () {
            Route::get('/payment_info', 'paymentInfo')->name('payment_info');
            Route::get('/balance', 'balance')->name('balance');
        });

        Route::get('/users/worker/get/info', [AdminUserController::class, 'getWorkerInfo'])->name('get_worker_info');
        Route::post('/users/worker/edit/set_citizenship',  [AdminUserController::class, 'setCitizenship'])->name('setCitizenship');
        Route::post('/users/worker/edit/set_passport',  [AdminUserController::class, 'setPassport'])->name('setPassport');
        Route::post('/users/worker/edit/set_files',  [AdminUserController::class, 'setWorkerFiles'])->name('setWorkerFiles');
        Route::post('/users/worker/edit/set_bank_card',  [AdminUserController::class, 'setBankCard'])->name('setBankCard');
    });

    Route::middleware(['check_role:WORKER'])->group(function () {
        Route::controller(PersonalJobController::class)->group(function () {
            Route::get('/jobs', 'jobs')->name('jobs');
            Route::post('/accept_job', 'acceptJob')->name('accept_job');
            Route::post('/jobs/add_to_favorite', 'addToFavorite')->name('add_to_favorite');
            Route::get('/filter_map', 'filterMap')->name('filter_map');
            Route::get('/jobs/show_list/{id}', 'showList')->name('job_show_list');
        });

        Route::controller(PersonalProfileController::class)->group(function () {
            // Route::get('/profile', 'profile')->name('profile');
            Route::post('/set_preferred_positions', 'setPreferredPositions');
            Route::post('/set_experiences', 'setExperiences');
            Route::post('/set_field_of_activities', 'setFieldOfActivities');
            Route::post('/set_educations', 'setEducations');
            Route::post('/set_professional_skills', 'setProfessionalSkills');
            Route::post('/set_personal_skills', 'setPersonalSkills');
            Route::post('/set_language_skills', 'setLanguageSkills');
            Route::post('/set_driver_licenses', 'setDriverLicenses');
            Route::post('/set_hobbies', 'setHobbies');
            Route::post('/set_user_info', 'setUserInfo');
        });

        Route::controller(PersonalInformationController::class)->group(function () {
            Route::get('/informations', 'informations')->name('informations');
            Route::post('/set_information', 'setInformation')->name('set_information');
        });

        Route::controller(PersonalCheckListController::class)->group(function () {
            Route::get('/my_check_lists', 'checkLists')->name('my_check_lists');
            Route::get('/schedule', 'schedule')->name('schedule');
        });
    });

    Route::middleware(['check_role:WORKER'])->group(function () {

    });

    Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->name('dashboard');

        Route::middleware(['admin'])->group(function () {
            Route::resource('positions', AdminPositionController::class);
            Route::resource('educations', AdminEducationController::class);
            Route::resource('professional_skills', AdminProfessionalSkillController::class);
            Route::resource('personal_skills', AdminPersonalSkillController::class);
            Route::resource('language_skills', AdminLanguageSkillController::class);
            Route::resource('field_of_activities', AdminFieldOfActivityController::class);
            Route::resource('driver_licenses', AdminDriverLicenseController::class);
            Route::resource('hobbies', AdminHobbyController::class);
            Route::resource('genders', AdminGenderController::class);
            Route::resource('citizenships', AdminCitizenshipController::class);
            Route::resource('industries', AdminIndustryController::class);
            Route::resource('company_types', AdminCompanyTypeController::class);
            Route::resource('quantity_employees', AdminQuantityEmployeController::class);
            Route::resource('countries', AdminCountryController::class);
            Route::resource('districts', AdminDistrictController::class);
            Route::resource('regions', AdminRegionController::class);
            Route::resource('territory_types', AdminTerritoryTypeController::class);
            Route::resource('cities', AdminCityController::class);
            Route::resource('advantage_employees', AdminAdvantageEmployeController::class);
            Route::resource('permissions', AdminPermissionController::class);
            Route::resource('informations', AdminInformationController::class);
            Route::resource('partners', AdminPartnerController::class);
            Route::resource('mean_of_transports', AdminMeanOfTransportController::class);
            Route::resource('currencies', AdminCurrencyController::class);
            Route::resource('jobs', AdminJobController::class);
            Route::resource('shift_reasons', AdminShiftReasonController::class);
            Route::resource('disability_groups', AdminDisabilityGroupController::class);
        });

        Route::resource('establishments', AdminEstablishmentController::class);
        Route::resource('check_lists', AdminCheckListController::class);
        Route::resource('black_lists', AdminBlackListController::class);
        Route::resource('users', AdminUserController::class);

        Route::get('/income', [AdminCheckListController::class, 'income'])->name('income');
        Route::get('/spending', [AdminCheckListController::class, 'spending'])->name('spending');
        Route::get('/check_list_bonuses', [AdminCheckListController::class, 'checkListBonuses'])->name('check_list_bonuses');
        Route::post('/check_list_bonuses/set', [AdminCheckListController::class, 'setCheckListBonuses'])->name('set_check_list_bonuses');

        Route::get('/employee_balance', [AdminEmployeeBalanceController::class, 'employeeBalance'])->name('employee_balance');
        Route::get('/employee_balance/detail/{id}', [AdminEmployeeBalanceController::class, 'employeeBalanceList'])->name('employee_balance_detail');
        Route::post('/employee_balance/confirm', [AdminEmployeeBalanceController::class, 'employeeBalanceConfirm'])->name('employee_balance_confirm');

        Route::get('/companies_balance', [AdminCompanyBalanceController::class, 'companiesBalance'])->name('companies_balance');
        Route::get('/login_history', [AdminUserLoginHistoryController::class, 'index'])->name('login_history');

        Route::get('/show_job_list/{id}', [AdminJobController::class, 'showJobList'])->name('show_job_list');

        Route::get('/districts/country/{country_id}', [AdminDistrictController::class, 'getDistrictsByCountry'])->name('get_districts_by_country');
        Route::get('/regions/district/{district_id}', [AdminRegionController::class, 'getRegionsByDistrict'])->name('get_regions_by_district');

        Route::get('/users/get_branches/list', [AdminUserController::class, 'getBrancheLists'])->name('get_user_branche_lists');
        Route::get('/users/get_statuses/{user_id}', [AdminUserController::class, 'getStatuses'])->name('get_user_statuses');
        Route::post('/users/add_status', [AdminUserController::class, 'addStatus'])->name('add_user_status');
        Route::get('/users/employees/get_all', [AdminUserController::class, 'getAllEmployees'])->name('get_all_employees');
        Route::post('/users/employees/set', [AdminUserController::class, 'setEmployees'])->name('set_employees');

        Route::post('/establishments/set_status', [AdminEstablishmentController::class, 'setStatus'])->name('set_establishment_status');

        Route::get('/users/company/get/info', [AdminUserController::class, 'getCompanyInfo'])->name('get_company_info');
        Route::post('/users/company/edit/set_who_are_we',  [AdminUserController::class, 'setWhoAreWe'])->name('setWhoAreWe');
        Route::post('/users/company/edit/set_industry',  [AdminUserController::class, 'setIndustry'])->name('setWhoAreWe');
        Route::post('/users/company/edit/set_contact',  [AdminUserController::class, 'setContact'])->name('setWhoAreWe');
        Route::post('/users/company/edit/set_advantage',  [AdminUserController::class, 'setAdvantage'])->name('setWhoAreWe');

        Route::post('/users/company/edit/set_card',  [AdminUserController::class, 'setCard'])->name('setCard');
        Route::post('/users/company/edit/set_account',  [AdminUserController::class, 'setAccount'])->name('setAccount');
        Route::post('/users/company/edit/set_files',  [AdminUserController::class, 'setFiles'])->name('setFiles');

        Route::controller(AdminTBankController::class)->group(function () {
            Route::post('/add_employee_recipient_post', 'addEmployeeRecipientPost')->name('add_employee_recipient_post');
            Route::post('/salary_create_employee_post', 'salaryCreateEmployeePost')->name('salary_create_employee_post');
            Route::get('/get_operations_info', 'getOperationsInfo')->name('get_operations_info');
            Route::get('/add_employee_recipient_result', 'addEmployeeRecipientResult')->name('add_employee_recipient_result');
            Route::get('/get_salary_create_employee_result', 'getSalaryCreateEmployeeResult')->name('get_salary_create_employee_result');

            Route::post('/salary_get_employee_list_post', 'salaryGetEmployeeListPost')->name('salary_get_employee_list_post');

            Route::post('/salary_create_payment_registry_post', 'salaryCreatePaymentRegistryPost')->name('salary_create_payment_registry_post');

            Route::get('/salary_get_payment_registry_create_result', 'salaryGetPaymentRegistryCreateResult')->name('salary_get_payment_registry_create_result');
            Route::get('/salary_get_payment_registry', 'salaryGetPaymentRegistry')->name('salary_get_payment_registry');

            Route::post('/salary_payment_registry_submit_post', 'salaryPaymentRegistrySubmitPost')->name('salary_payment_registry_submit_post');
            Route::get('/salary_payment_registry_submit_result', 'salaryPaymentRegistrySubmitResult')->name('salary_payment_registry_submit_result');
        });

        Route::controller(AdminSalaryProjectController::class)->group(function () {
            Route::get('/salary_project_results', 'salaryProjectResults')->name('salary_project_results');
        });
    });
});
