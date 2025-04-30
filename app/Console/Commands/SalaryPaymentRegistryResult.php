<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\EmployeeBalance,
    App\Models\EmployeeBalanceList,
    App\Models\EmployeeBalanceListTransfer;

class SalaryPaymentRegistryResult extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salaryPaymentRegistryResult';

    const TOKEN = 'TBankSandboxToken';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $employeeBalanceListTransfers = EmployeeBalanceListTransfer::whereNull('payment_registry_id')->get();
        foreach($employeeBalanceListTransfers as $transfer) {

            if($transfer->request_log) {
                $requestLog = json_decode($transfer->request_log, true);

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . self::TOKEN,
                    'Accept' => 'application/json',
                ])->get('https://business.tbank.ru/openapi/sandbox/api/v1/salary/payment-registry/create/result', [
                    'correlationId' => $requestLog['correlationId']
                ])->json();

                if(isset($response['status'])) {
                    if(isset($response['paymentRegistryId']) && $response['paymentRegistryId']) {
                        $transfer->update(['payment_registry_id' => $response['paymentRegistryId']]);
                    }

                    $transfer->update([
                        'sgprcr_status'       => $response['status'],
                        'sgprcr_response_log' => json_encode($response),
                        'sgprcr_at'           => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        $employeeBalanceListTransferSPRS = EmployeeBalanceListTransfer::whereNotNull('payment_registry_id')->where('is_sprs_signed', 0)->get();

        foreach($employeeBalanceListTransferSPRS as $transfer) {
            $requestLog = json_decode($transfer->request_log, true);

            $data = [
                'correlationId'     => $requestLog['correlationId'],
                'paymentRegistryId' => (int) $transfer->payment_registry_id
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . self::TOKEN,
                'Accept' => 'application/json',
            ])->withOptions([
                'verify' => false,
            ])->post('https://business.tbank.ru/openapi/sandbox/secured/api/v1/salary/payment-registry/submit', $data)->json();

            $is_sprs_signed = 0;
            if(isset($response['correlationId']) && $response['correlationId']) {
                $is_sprs_signed = 1;
            }

            $transfer->update([
                'is_sprs_signed'   => $is_sprs_signed,
                'sprs_at'           => date('Y-m-d H:i:s'),
                'sprs_response_log' => json_encode($data),
                'sprs_request_log'  => $response ? json_encode($response) : null,
            ]);

        }

        return Command::SUCCESS;

    }
}
