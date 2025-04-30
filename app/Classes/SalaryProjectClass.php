<?php

namespace App\Classes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use App\Models\BankOperationLog,
    App\Models\EmployeeBalanceListTransfer,
    App\Models\EmployeeBalanceList,
    App\Models\User;
use Log;

class SalaryProjectClass {

    const IS_PROD = false;
    const TOKEN = 'TBankSandboxToken';
    const ADD_EMPLOYEE_RECIPIENT = 'https://business.tbank.ru/openapi/sandbox/api/v1/employees/add/by-requisites'; // Добавить сотрудника по реквизитам
    const ADD_EMPLOYEE_RECIPIENT_RESULT = 'https://business.tbank.ru/openapi/sandbox/api/v1/employees/add/by-requisites/result'; // Получить результат добавления сотрудника по реквизитам
    const SALARY_GET_PAYMENT_REGISTRY_LIST = 'https://business.tbank.ru/openapi/sandbox/secured/api/v1/salary/payment-registry/list'; // Получить список платежных реестров за временной промежуток
    const SALARY_CREATE_EMPLOYEE = 'https://business.tbank.ru/openapi/sandbox/api/v1/salary/employees/create'; // Создать черновики анкет сотрудников
    const SALARY_GET_EMPLOYEE_CREATE_RESULT = 'https://business.tbank.ru/openapi/sandbox/api/v1/salary/employees/create/result'; // Получить результат создания черновиков анкет сотрудников
    const SALARY_GET_EMPLOYEES_LIST = 'https://business.tbank.ru/openapi/sandbox/api/v1/salary/employees/list'; // Получить информацию по сотрудникам
    const SALARY_CREATE_PAYMENT_REGISTRY = 'https://business.tbank.ru/openapi/sandbox/api/v1/salary/payment-registry/create'; // Создать черновик платежного реестра

    const SALARY_GET_PAYMENT_REGISTRY_CREATE_RESULT = 'https://business.tbank.ru/openapi/sandbox/api/v1/salary/payment-registry/create/result'; // Получить результат создания черновика платежного реестра
    const SALARY_GET_PAYMENT_REGISTRY = 'https://business.tbank.ru/openapi/sandbox/api/v1/salary/payment-registry'; // Получить информацию по платежному реестру
    const SALARY_PAYMENT_REGISTRY_SUBMIT = 'https://business.tbank.ru/openapi/sandbox/secured/api/v1/salary/payment-registry/submit'; // Подписать платежный реестр сотрудников
    const SALARY_PAYMENT_REGISTRY_SUBMIT_RESULT = 'https://business.tbank.ru/openapi/sandbox/secured/api/v1/salary/payment-registry/submit/result'; // Получить результат подписания платежного реестра сотрудников
    const CREATE_SALARY_REGISTRY_PAYMENT = 'https://business.tbank.ru/openapi/sandbox/secured/api/v1/payment/payment-registry/pay'; // Оплатить реестр

    public function addEmployeeRecipient($user) {
        $uuid = Str::uuid()->toString();
        $data = [
            'correlationId' => $uuid,
            'employees' => [
                [
                    'number'    => self::IS_PROD ? $user->id : 1,
                    'firstName' => self::IS_PROD ? $user->workerInfo->first_name['ru'] : 'string',
                    'lastName'  => self::IS_PROD ? $user->workerInfo->last_name['ru'] : 'string',
                    'bankInfo'  => [
                        'bankBic'       => 'string',
                        'accountNumber' => 'string',
                    ]
                ],
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . self::TOKEN,
            'Accept' => 'application/json',
        ])->withOptions([
            'verify' => false,
        ])->post(self::ADD_EMPLOYEE_RECIPIENT, $data)->json();

        $status = 'ERROR';

        if(isset($response['correlationId']) && $response['correlationId']) {
            $status = 'SUCCESS';
        }

        BankOperationLog::create([
            'status'                  => $status,
            'type'                    => 'ADD_RECIPIENT',
            'user_id'                 => $user->id,
            'sended_correlation_id'   => $uuid,
            'received_correlation_id' => $response['correlationId'],
            'request_log'             => json_encode($data),
            'response_log'            => json_encode($response),
        ]);

        return [
            'status' => $status
        ];
    }

    public function addEmployeeRecipientResult($employeeCorrelationId) {
        $correlationId = self::IS_PROD ? $employeeCorrelationId : '71f21e8c-7f71-49be-8775-13828f0bec82';
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . self::TOKEN,
            'Accept' => 'application/json',
        ])->get(self::ADD_EMPLOYEE_RECIPIENT_RESULT, [
            'correlationId' => $correlationId
        ])->json();
    }

    public function getSalaryCreateEmployeeResult($employeeCorrelationId) {
        $correlationId = self::IS_PROD ? $employeeCorrelationId : '71f21e8c-7f71-49be-8775-13828f0bec82';
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . self::TOKEN,
            'Accept' => 'application/json',
        ])->get(self::SALARY_GET_EMPLOYEE_CREATE_RESULT, [
            'correlationId' => $correlationId
        ])->json();
    }

    public function salaryGetPaymentRegistryList($periodStart, $periodEnd) {
        $data = [
            'offset'      => 0,
            'statuses'    => [],
            'periodStart' => $periodStart,
            'periodEnd'   => $periodEnd,
        ];

        return Http::withHeaders([
            'Authorization' => 'Bearer ' . self::TOKEN,
            'Accept' => 'application/json',
        ])->withOptions([
            'verify' => false,
        ])->post(self::SALARY_GET_PAYMENT_REGISTRY_LIST, $data)->json();
    }

    public function salaryCreateEmployee($user) {
        $uuid = Str::uuid()->toString();

        $data = [
            'correlationId' => $uuid,
            'employees' => [
                [
                    'number'      => self::IS_PROD ? $user->id : 1,
                    'firstName'   => self::IS_PROD && isset($user->workerInfo->first_name['ru']) ? $user->workerInfo->first_name['ru'] : 'string',
                    'lastName'    => self::IS_PROD && isset($user->workerInfo->last_name['ru']) ? $user->workerInfo->last_name['ru'] : 'string',
                    'birthDate'   => self::IS_PROD && isset($user->workerInfo->birthday) ? $user->workerInfo->birthday : '2024-10-21',
                    'birthPlace'  => self::IS_PROD && isset($user->workerInfo->place_of_birth['ru']) ? $user->workerInfo->place_of_birth['ru'] : 'string',
                    'citizenship' => self::IS_PROD && isset($user->workerInfo->citizenship?->name['ru']) ? $user->workerInfo->citizenship?->name['ru'] : 'string',
                    'phones'      => [],
                    'addresses'   => [],
                    'documents'   => [],
                    'jobInfo'     => [
                        'position' => self::IS_PROD && isset($user->positions[0]->name['ru']) ? $user->positions[0]->name['ru'] : 'string',
                    ]
                ],
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . self::TOKEN,
            'Accept' => 'application/json',
        ])->withOptions([
            'verify' => false,
        ])->post(self::SALARY_CREATE_EMPLOYEE, $data)->json();

        $status = 'ERROR';

        if(isset($response['correlationId']) && $response['correlationId']) {
            $status = 'SUCCESS';
        }

        BankOperationLog::create([
            'status'                  => $status,
            'type'                    => 'SALARY_CREATE_EMPLOYEE',
            'user_id'                 => $user->id,
            'sended_correlation_id'   => $uuid,
            'received_correlation_id' => $response['correlationId'],
            'request_log'             => json_encode($data),
            'response_log'            => json_encode($response),
        ]);

        return [
            'status' => $status
        ];
    }

    public function salaryGetEmployeesList($user) {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . self::TOKEN,
            'Accept' => 'application/json',
        ])->withOptions([
            'verify' => false,
        ])->post(self::SALARY_GET_EMPLOYEES_LIST, ['employeeIds' => [$user->id]])->json();
    }

    public function salaryCreatePaymentRegistry($user) {
        $uuid = Str::uuid()->toString();
        $data = [
            'correlationId' => $uuid,
            // 'companyAccountNumber' => '',
            // 'loadDate' => '',
            // 'registryCreateType' => '',
            'payments' => [
                [
                    'number'    => self::IS_PROD ? $user->id : 1,
                    'accountNumber' => self::IS_PROD ? $user->workerInfo->first_name['ru'] : 'string',
                    'paymentPurpose'  => self::IS_PROD ? $user->workerInfo->last_name['ru'] : 'string',
                    'employeeInfo'  => [
                        'firstName'   => self::IS_PROD && isset($user->workerInfo->first_name['ru']) ? $user->workerInfo->first_name['ru'] : 'string',
                        'lastName'    => self::IS_PROD && isset($user->workerInfo->last_name['ru']) ? $user->workerInfo->last_name['ru'] : 'string',
                    ],
                    'sum' => self::IS_PROD ? $user->id : 1,
                ],
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . self::TOKEN,
            'Accept' => 'application/json',
        ])->withOptions([
            'verify' => false,
        ])->post(self::SALARY_CREATE_PAYMENT_REGISTRY, $data)->json();

        $status = 'ERROR';

        if(isset($response['correlationId']) && $response['correlationId']) {
            $status = 'SUCCESS';
        }

        BankOperationLog::create([
            'status'                  => $status,
            'type'                    => 'SALARY_PAYMENT_REGISTRY',
            'user_id'                 => $user->id,
            'sended_correlation_id'   => $uuid,
            'received_correlation_id' => $response['correlationId'],
            'request_log'             => json_encode($data),
            'response_log'            => json_encode($response),
        ]);

        return [
            'status' => $status
        ];
    }

    public function salaryCreatePaymentRegistryNew($employeeId, $employeeBalanceListId) {
        $uuid = Str::uuid()->toString();

        $user = User::select('id', 'name')->with([
            'workerInfo' => function ($query) {
                $query->select('id', 'user_id', 'first_name', 'last_name', 'surname', 'birthday', 'place_of_birth', 'series_and_number', 'account_number', 'bik')->with('citizenship', function($q) {
                    $q->select('id', 'name');
                });
            }
        ])->with('positions')->find($employeeId);

        // dd($user);

        $employeeBalanceList = EmployeeBalanceList::find($employeeBalanceListId);

        // dd($user);

        $data = [
            'correlationId' => $uuid,
            // 'companyAccountNumber' => '',
            // 'loadDate' => '',
            // 'registryCreateType' => '',
            'payments' => [
                [
                    'number'         => $employeeBalanceListId,
                    'accountNumber'  => $user->workerInfo->account_number,
                    'paymentPurpose' => 'Зарплата',
                    'employeeInfo'   => [
                        'firstName'   => self::IS_PROD && isset($user->workerInfo->first_name['ru']) ? $user->workerInfo->first_name['ru'] : 'string',
                        'lastName'    => self::IS_PROD && isset($user->workerInfo->last_name['ru']) ? $user->workerInfo->last_name['ru'] : 'string',
                    ],
                    'sum' => $employeeBalanceList->amount,
                ],
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . self::TOKEN,
            'Accept' => 'application/json',
        ])->withOptions([
            'verify' => false,
        ])->post(self::SALARY_CREATE_PAYMENT_REGISTRY, $data)->json();

        $status = 'ERROR';

        if(isset($response['correlationId']) && $response['correlationId']) {
            $status = 'SUCCESS';
        }

        BankOperationLog::create([
            'status'                  => $status,
            'type'                    => 'SALARY_PAYMENT_REGISTRY',
            'user_id'                 => $user->id,
            'sended_correlation_id'   => $uuid,
            'received_correlation_id' => isset($response['correlationId']) ? $response['correlationId'] : null,
            'request_log'             => json_encode($data),
            'response_log'            => json_encode($response),
        ]);

        EmployeeBalanceListTransfer::create([
            'employee_balance_list_id' => $employeeBalanceListId,
            'status'                   => 'PENDING',
            'request_log'              => json_encode($data),
            'response_log'             => json_encode($response),
        ]);

        return [
            'status' => $status
        ];
    }

    public function salaryGetPaymentRegistryCreateResult($employeeCorrelationId, $user) {
        $correlationId = self::IS_PROD ? $employeeCorrelationId : '71f21e8c-7f71-49be-8775-13828f0bec82';
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . self::TOKEN,
            'Accept' => 'application/json',
        ])->get(self::SALARY_GET_PAYMENT_REGISTRY_CREATE_RESULT, [
            'correlationId' => $correlationId
        ])->json();

        $status = 'ERROR';

        if(isset($response['status']) && $response['status'] == 'CREATED') {
            $status = 'SUCCESS';
            BankOperationLog::create([
                'status'                  => $status,
                'type'                    => 'SALARY_PAYMENT_REGISTRY_RESULT',
                'user_id'                 => $user->id,
                'sended_correlation_id'   => null,
                'received_correlation_id' => null,
                'request_log'             => null,
                'response_log'            => json_encode($response),
            ]);
        }

        return $response;
    }

    public function salaryGetPaymentRegistry($paymentRegistryId) {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . self::TOKEN,
            'Accept' => 'application/json',
        ])->get(self::SALARY_GET_PAYMENT_REGISTRY . '/' . $paymentRegistryId)->json();
    }

    public function salaryPaymentRegistrySubmit($user, $employeeCorrelationId, $paymentRegistryId) {
        $data = [
            'correlationId'     => $employeeCorrelationId,
            'paymentRegistryId' => $paymentRegistryId
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . self::TOKEN,
            'Accept' => 'application/json',
        ])->withOptions([
            'verify' => false,
        ])->post(self::SALARY_PAYMENT_REGISTRY_SUBMIT, $data)->json();

        $status = 'ERROR';

        if(isset($response['correlationId']) && $response['correlationId']) {
            $status = 'SUCCESS';
        }

        BankOperationLog::create([
            'status'                  => $status,
            'type'                    => 'SALARY_PAYMENT_REGISTRY_SUBMIT',
            'user_id'                 => $user->id,
            'sended_correlation_id'   => $employeeCorrelationId,
            'received_correlation_id' => $response['correlationId'],
            'request_log'             => json_encode($data),
            'response_log'            => json_encode($response),
        ]);

        return [
            'status' => $status
        ];
    }

    public function salaryPaymentRegistrySubmitResult($employeeCorrelationId) {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . self::TOKEN,
            'Accept' => 'application/json',
        ])->get(self::SALARY_PAYMENT_REGISTRY_SUBMIT_RESULT, [
            'correlationId' => $employeeCorrelationId
        ])->json();
    }

    public function createSalaryRegistryPayment() {

        $data = [
            'correlationId' => $uuid,
            'employees' => [
                [
                    'number'    => self::IS_PROD ? $user->id : 1,
                    'firstName' => self::IS_PROD ? $user->workerInfo->first_name['ru'] : 'string',
                    'lastName'  => self::IS_PROD ? $user->workerInfo->last_name['ru'] : 'string',
                    'bankInfo'  => [
                        'bankBic'       => 'string',
                        'accountNumber' => 'string',
                    ]
                ],
            ],
        ];

    }

}
