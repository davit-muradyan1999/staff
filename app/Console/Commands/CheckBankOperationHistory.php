<?php

namespace App\Console\Commands;

use Log;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\CompanyBalanceList,
    App\Models\CompanyInfo,
    App\Models\CompanyTransfer;

class CheckBankOperationHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkBankOperationHistory';

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
        $operations = Http::withHeaders([
            'Authorization' => 'Bearer TBankSandboxToken',
            'Accept' => 'application/json',
        ])->get('https://business.tbank.ru/openapi/sandbox/api/v1/statement', [
            'accountNumber' => '40702810510000710417',
            'from' => '2024-02-01T21:00:00Z',
        ])->json();

        Log::info($operations);

        if(isset($operations['operations'])) {
            foreach($operations['operations'] as $operation) {
                if(!isset($operation['operationId']) || (isset($operation['operationId']) && !$operation['operationId'])) {
                    continue;
                }

                $companyBalanceList = CompanyBalanceList::where('operation_id', $operation['operationId'])->first();

                if(!$companyBalanceList) {

                    $companyInfo = CompanyInfo::where('checking_account', 'like', '%' . 11111 . '%')->first();

                    if($companyInfo) {
                        CompanyBalanceList::create([
                            'type'         => 'TRANSFER',
                            'action'       => 'PLUS',
                            'operation_id' => $operation['operationId'],
                            'company_id'   => $companyInfo->user_id,
                            'amount'       => $operation['operationAmount'],
                        ]);

                        CompanyTransfer::create([
                            'status'       => 'SUCCESS',
                            'operation_id' => $operation['operationId'],
                            'response'     => $operation,
                        ]);
                    }
                }
            }
        }

        return true;
    }
}
