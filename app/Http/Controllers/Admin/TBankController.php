<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Classes\SalaryProjectClass;
use App\Models\User,
    App\Models\BankOperationLog;

class TBankController extends Controller
{
    public function getOperationsInfo(Request $request) {
        $addRecipientInfo = BankOperationLog::where('type', 'ADD_RECIPIENT')->where('user_id', $request['user_id'])->orderBy('id', 'desc')->first();
        $salaryCreateEmployeeInfo = BankOperationLog::where('type', 'SALARY_CREATE_EMPLOYEE')->where('user_id', $request['user_id'])->orderBy('id', 'desc')->first();
        $salaryCreatePaymentRegistryInfo = BankOperationLog::where('type', 'SALARY_PAYMENT_REGISTRY')->where('user_id', $request['user_id'])->orderBy('id', 'desc')->first();
        $salaryCreatePaymentRegistryResultInfo = BankOperationLog::where('type', 'SALARY_PAYMENT_REGISTRY_RESULT')->where('user_id', $request['user_id'])->orderBy('id', 'desc')->first();
        $salaryPaymentRegistrySubmitInfo = BankOperationLog::where('type', 'SALARY_PAYMENT_REGISTRY_SUBMIT')->where('user_id', $request['user_id'])->orderBy('id', 'desc')->first();

        return response()->json([
            'addRecipientInfo'                      => $addRecipientInfo,
            'salaryCreateEmployeeInfo'              => $salaryCreateEmployeeInfo,
            'salaryCreatePaymentRegistryInfo'       => $salaryCreatePaymentRegistryInfo,
            'salaryCreatePaymentRegistryResultInfo' => $salaryCreatePaymentRegistryResultInfo,
            'salaryPaymentRegistrySubmitInfo'       => $salaryPaymentRegistrySubmitInfo,
        ], 200);
    }

    public function addEmployeeRecipientPost(Request $request) {
        $user = User::select('id', 'name')->with([
            'workerInfo' => function ($query) {
                $query->select('id', 'user_id', 'first_name', 'last_name', 'surname', 'birthday', 'series_and_number', 'account_number', 'bik');
            }
        ])->find($request->user_id);

        if($user) {
            $salaryProject = new SalaryProjectClass($user);
            $addWorkerToProject = $salaryProject->addEmployeeRecipient($user);
        }

        if($addWorkerToProject['status'] == 'SUCCESS')
            return response()->json(['message' => __('general.successfully_added')], 200);

        return response()->json(['message' => __('general.not_added')], 500);
    }

    public function addEmployeeRecipientResult(Request $request) {
        return (new SalaryProjectClass())->addEmployeeRecipientResult($request->correlation_id);
    }

    public function getSalaryCreateEmployeeResult(Request $request) {
        return (new SalaryProjectClass())->getSalaryCreateEmployeeResult($request->correlation_id);
    }

    public function salaryCreateEmployeePost(Request $request) {
        $user = User::select('id', 'name')->with([
            'workerInfo' => function ($query) {
                $query->select('id', 'user_id', 'first_name', 'last_name', 'surname', 'birthday', 'place_of_birth', 'series_and_number', 'account_number', 'bik')->with('citizenship', function($q) {
                    $q->select('id', 'name');
                });
            }
        ])->with('positions')->find($request->user_id);

        if($user) {
            $salaryProject = new SalaryProjectClass($user);
            $addWorkerToProject = $salaryProject->salaryCreateEmployee($user);
        }

        if($addWorkerToProject['status'] == 'SUCCESS')
            return response()->json(['message' => __('general.successfully_added')], 200);

        return response()->json(['message' => __('general.not_added')], 500);
    }

    public function salaryGetEmployeeListPost(Request $request) {
        $user = User::select('id', 'name')->where('id', $request->user_id)->first();
        return $user
                ? (new SalaryProjectClass())->salaryGetEmployeesList($user)
                : response()->json(['message' => __('general.not_added')], 500);
    }

    public function salaryCreatePaymentRegistryPost(Request $request) {
        $user = User::select('id', 'name')->with([
            'workerInfo' => function ($query) {
                $query->select('id', 'user_id', 'first_name', 'last_name', 'surname', 'birthday', 'series_and_number', 'account_number', 'bik');
            }
        ])->find($request->user_id);

        if($user) {
            $salaryProject = new SalaryProjectClass($user);
            $addWorkerToProject = $salaryProject->salaryCreatePaymentRegistry($user);
        }

        if($addWorkerToProject['status'] == 'SUCCESS')
            return response()->json(['message' => __('general.successfully_added')], 200);

        return response()->json(['message' => __('general.not_added')], 500);
    }

    public function salaryGetPaymentRegistryCreateResult(Request $request) {
        $user = User::select('id', 'name')->find($request->user_id);

        if($user) {
            return (new SalaryProjectClass())->salaryGetPaymentRegistryCreateResult($request->correlation_id, $user);
        }

        return response()->json(['message' => __('general.not_added')], 500);
    }

    public function salaryGetPaymentRegistry(Request $request) {
        return (new SalaryProjectClass())->salaryGetPaymentRegistry($request->payment_registry_id);
    }

    public function salaryPaymentRegistrySubmitPost(Request $request) {
        $user = User::select('id', 'name')->find($request->userId);

        if($user) {
            $salaryProject = new SalaryProjectClass($user);
            $addWorkerToProject = $salaryProject->salaryPaymentRegistrySubmit($user, $request['correlationId'], $request['paymentRegistryId']);
        }

        if($addWorkerToProject['status'] == 'SUCCESS')
            return response()->json(['message' => __('general.successfully_added')], 200);

        return response()->json(['message' => __('general.not_added')], 500);
    }

    public function salaryPaymentRegistrySubmitResult(Request $request) {
        $user = User::select('id', 'name')->find($request->user_id);

        if($user) {
            return (new SalaryProjectClass())->salaryPaymentRegistrySubmitResult($request->correlation_id, $user);
        }

        return response()->json(['message' => __('general.not_added')], 500);
    }
}
