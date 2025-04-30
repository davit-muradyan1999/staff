<?php

namespace App\Other;

use Log;
use DB;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Models\EmployeeBalance,
    App\Models\CompanyBalance,
    App\Models\CompanyBalanceList,
    App\Models\EmployeeBalanceList;

class Misc {

    public function setEmployeeBalance($type, $action, $companyId, $jobId, $jobCheckListId, $employeeId, $totalHours, $totalSalary) {
        $employeeBalance = EmployeeBalance::where('employee_user_id', $employeeId)->first();

        if($action == 'PLUS')       $totalSalary = 0 + $totalSalary;
        else if($action == 'MINUS') $totalSalary = 0 - $totalSalary;

        if(!$employeeBalance) {
            EmployeeBalance::create([
                'employee_user_id' => $employeeId,
                'time'             => $totalHours,
                'balance'          => $totalSalary,
            ]);
        } else {
            $timeValue = $employeeBalance->time + $totalHours;
            $balanceValue = $employeeBalance->balance + $totalSalary;

            $employeeBalance->update([
                'time'             => $timeValue,
                'balance'          => $balanceValue,
            ]);
        }

        EmployeeBalanceList::create([
            'type'              => $type,
            'action'            => $action,
            'company_id'        => $companyId,
            'employee_id'       => $employeeId,
            'job_id'            => $jobId,
            'job_check_list_id' => $jobCheckListId,
            'amount'            => $totalSalary,
        ]);
    }

    public function setCompanyBalance($type, $action, $companyId, $jobId, $jobCheckListId, $employeeId, $totalHours, $totalSalary) {
        $companyBalance = CompanyBalance::where('company_id', $companyId)->first();

        if($action == 'PLUS')       $totalSalary = 0 + $totalSalary;
        else if($action == 'MINUS') $totalSalary = 0 - $totalSalary;

        if(!$companyBalance) {
            CompanyBalance::create([
                'company_id' => $companyId,
                'balance'    => $totalSalary,
            ]);
        } else {
            $balanceValue = $companyBalance->balance + $totalSalary;

            $companyBalance->update([
                'balance'          => $balanceValue,
            ]);
        }

        CompanyBalanceList::create([
            'type'              => $type,
            'action'            => $action,
            'company_id'        => $companyId,
            'employee_id'       => $employeeId,
            'job_id'            => $jobId,
            'job_check_list_id' => $jobCheckListId,
            'amount'            => $totalSalary,
        ]);
    }

    public function isCanSeeAllBranches() {
        $allowedPermissions = [1, 3];
        $permissions = Auth::user()->permissions->pluck('id');

        if($permissions) {
            foreach($allowedPermissions as $p_1) {
                foreach($permissions as $p_2) {
                    if($p_1 == $p_2) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function getModeratorBranches() {
        return Auth::user()->branches->pluck('id');
    }

}
