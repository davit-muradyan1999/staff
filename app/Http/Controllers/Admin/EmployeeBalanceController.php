<?php

namespace App\Http\Controllers\Admin;

use DB;
use Log;
use Auth;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Classes\SalaryProjectClass;
use App\Models\User,
    App\Models\Transfer,
    App\Models\EmployeeBalance,
    App\Models\EmployeeBalanceList,
    App\Models\TransferDetail;

class EmployeeBalanceController extends Controller
{
    public function employeeBalance(Request $request)
    {

        if(Auth::user()->role == 'ADMINISTRATOR' && in_array(8, Auth::user()->permissions->pluck('id')->toArray()))
            return redirect()->route('admin.dashboard');

        $startedAt = $request->startedAt ? date("Y-m-d", strtotime($request->startedAt)) . ' 00:00:00' : '';
        $finishedAt = $request->finishedAt ? date("Y-m-d", strtotime($request->finishedAt)) . ' 23:59:59' : '';
        $bonus = $request->bonus == '1' ? 1 : ($request->bonus == '0' ? 0 : 1);
        $onlySupportedUsers = Auth::user()->role == 'ADMINISTRATOR' ? true : false;

        $temp_1 = '';
        $temp_2 = '';

        if($startedAt) {
            $temp_1 = " && job_check_lists.started_at >= '$startedAt'";
        }
        if($finishedAt) {
            $temp_2 = " && job_check_lists.finished_at <= '$finishedAt'";
        }

        $employees = User::select([
            'users.id',
            'users.name',
            'OldestShift.started_at as o_started_at',
            'LatestShift.finished_at as l_finished_at'
        ])->with([
            'employeeBalance',
        ])
        ->when($request->text, function ($q) use ($request) {
            return $q->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->text . '%');
            });
        })
        ->leftjoin('employee_balance_lists', function($join) use($bonus) {
            $join->on('employee_balance_lists.employee_id', '=', 'users.id');
            $join->where('employee_balance_lists.is_paid', '=', 0);
            $join->whereNull('employee_balance_lists.deleted_at');

            // if(!$bonus) {
            //     $join->whereIn('employee_balance_lists.type', ['SHIFT', 'TRANSFER']);
            // }
        })
        ->leftJoin(DB::raw("(
            Select min(job_check_lists.started_at) as started_at, min(job_check_lists.finished_at) as finished_at, job_check_lists.id, job_check_lists.employee_user_id
                from job_check_lists
            INNER JOIN employee_balance_lists ON employee_balance_lists.job_check_list_id = job_check_lists.id && employee_balance_lists.is_paid = 0
                where job_check_lists.deleted_at is null $temp_1 $temp_2 group by employee_user_id) OldestShift"
        ), function($join) use($startedAt, $finishedAt) {
            $join->on('OldestShift.employee_user_id', '=', 'employee_balance_lists.employee_id');
            // $join->on('OldestShift.id', '=', 'employee_balance_lists.job_check_list_id');

            // if($startedAt) {
            //     $join->where('job_check_lists.started_at', '>=', $startedAt);
            // }
            // if($finishedAt) {
            //     $join->where('job_check_lists.finished_at', '<=', $finishedAt);
            // }
        })
        ->leftJoin(DB::raw("(
            Select max(job_check_lists.started_at) as started_at, max(job_check_lists.finished_at) as finished_at, job_check_lists.id, job_check_lists.employee_user_id
                from job_check_lists
            INNER JOIN employee_balance_lists ON employee_balance_lists.job_check_list_id = job_check_lists.id && employee_balance_lists.is_paid = 0
                where job_check_lists.deleted_at is null $temp_1 $temp_2 group by employee_user_id) LatestShift"
        ), function($join) use($startedAt, $finishedAt) {
            $join->on('LatestShift.employee_user_id', '=', 'employee_balance_lists.employee_id');
            // $join->on('LatestShift.id', '=', 'employee_balance_lists.job_check_list_id');

            // if($startedAt) {
            //     $join->where('job_check_lists.started_at', '>=', $startedAt);
            // }
            // if($finishedAt) {
            //     $join->where('job_check_lists.finished_at', '<=', $finishedAt);
            // }
        })
        ->when($onlySupportedUsers, function ($q) use ($onlySupportedUsers) {
            $q->where(function ($q) {
                $q->whereHas('userSupports', function (Builder $query) {
                    $query->where('support_user_id', Auth::user()->id);
                });
            });
        })
        ->where('role', 'WORKER')
        ->groupBy('users.id');

        $notPaidEmployees = clone $employees;
        $notPaidEmployees = $notPaidEmployees->where(function ($q) use ($bonus) {
                                            $q->where(function ($q) use ($bonus) {
                                                $q->whereHas('employeeBalanceList', function (Builder $query) {
                                                    $query->where('is_paid', 0);
                                                })->orDoesntHave('employeeBalanceList');

                                                // if(!$bonus) {
                                                //     $q->where('type', ['SHIFT', 'TRANSFER']);
                                                // }
                                            });
                                        })
                                        ->withCount([
                                            'employeeBalanceList AS not_paid_sum' => function ($query) use($startedAt, $finishedAt, $bonus) {
                                                $query->select(DB::raw("SUM(TRUNCATE(amount, 4)) as not_paid_sum"))
                                                    ->where('is_paid', 0)
                                                    ->where(function ($q) use($bonus) {
                                                        if(!$bonus) {
                                                            $q->where('type', ['SHIFT', 'TRANSFER']);
                                                        }
                                                    })
                                                    ->where(function ($q) use($startedAt, $finishedAt, $bonus) {
                                                        $q->whereHas('jobCheckList', function (Builder $query) use($startedAt, $finishedAt, $bonus) {
                                                            if($startedAt) {
                                                                $query->where('started_at', '>=', $startedAt);
                                                            }
                                                            if($finishedAt) {
                                                                $query->where('finished_at', '<=', $finishedAt);
                                                            }
                                                        });

                                                        if($bonus) {
                                                            $q->orDoesntHave('jobCheckList');
                                                        }
                                                    });
                                            }
                                        ]);



        if($startedAt || $finishedAt) {
            $notPaidEmployees->whereHas('employeeBalanceList', function (Builder $query) use($startedAt, $finishedAt, $bonus) {
                $query
                ->where('is_paid', 0)
                ->where(function ($q) use($bonus) {
                    if(!$bonus) {
                        $q->where('type', ['SHIFT', 'TRANSFER']);
                    }
                })
                ->where(function ($q) use($startedAt, $finishedAt, $bonus) {
                    $q->whereHas('jobCheckList', function (Builder $query) use($startedAt, $finishedAt, $bonus) {
                        if($startedAt) {
                            $query->where('started_at', '>=', $startedAt);
                        }
                        if($finishedAt) {
                            $query->where('finished_at', '<=', $finishedAt);
                        }
                    });

                    if($bonus) {
                        $q->orDoesntHave('jobCheckList');
                    }
                });
            });
        }

        // if($finishedAt) {
        //     $notPaidEmployees->having('l_finished_at', '<=', $finishedAt . ' 23:59:59');
        // }

        $notPaidEmployeesCount = $notPaidEmployees->pluck('users.id')->count();
        $notPaidEmployees = $notPaidEmployees->paginate(50)->appends(request()->query());

        $tempTransfers = Transfer::with([
                                'payingUser' => function($q) {
                                    $q->select('id', 'name', 'company_name');
                                },
                                'paidEmployee' => function($q) {
                                    $q->select('id', 'name');
                                },
                                'details' => function($q) {
                                    $q->with([
                                        'employeeBalanceList' => function($q) {
                                            $q->with([
                                                'jobCheckList' => function($q) {
                                                    $q->with([
                                                        'company' => function ($q) {
                                                            $q->select('id', 'company_name');
                                                        },
                                                        'branche' => function ($q) {
                                                            $q->select('id', 'title->ru as title_b');
                                                        },
                                                        'job' => function ($q) {
                                                            $q->select('id', 'establishment_id')->with([
                                                                'establishment' => function($q) {
                                                                    $q->select('id', 'name->ru as name_e');
                                                                }
                                                            ]);
                                                        },
                                                        'jobTimeGraphic'
                                                    ]);
                                                }
                                            ]);
                                        }
                                    ]);
                                }
                            ])
                            ->when($request->text, function ($q) use ($request) {
                                return $q->where(function ($q) use ($request) {
                                    $q->whereHas('payingUser', function (Builder $query) use ($request) {
                                        $query->where('name', 'like', '%' . $request->text . '%');
                                    })->orWhereHas('paidEmployee', function (Builder $query) use ($request) {
                                        $query->where('name', 'like', '%' . $request->text . '%');
                                    });
                                });
                            })
                            ->when($startedAt, function ($q) use ($startedAt) {
                                return $q->where(function ($q) use ($startedAt) {
                                    $q->whereDate('created_at', '>=', $startedAt);
                                });
                            })->when($finishedAt, function ($q) use ($finishedAt) {
                                return $q->where(function ($q) use ($finishedAt) {
                                    $q->whereDate('created_at', '<=', $finishedAt);
                                });
                            })->when($onlySupportedUsers, function ($q) use ($onlySupportedUsers) {
                                $q->whereHas('paidEmployee', function (Builder $query) use ($onlySupportedUsers) {
                                    $query->whereHas('userSupports', function (Builder $query) use ($onlySupportedUsers) {
                                        $query->where('support_user_id', Auth::user()->id);
                                    });
                                });
                            })
                            ->orderBy('id', 'DESC');

        $transfers = clone $tempTransfers;

        $transfersCount = $transfers->count();
        $transfers = $transfers->paginate(50)->appends(request()->query());

        $companies = User::select('id', 'company_name')->where('role', 'COMPANY')->get();

        return Inertia::render('Admin/Libraries/EmployeeBalance/List', [
            'title'                 => __('general.employeeBalance'),
            'notPaidEmployees'      => $notPaidEmployees,
            'transfers'             => $transfers,
            'notPaidEmployeesCount' => $notPaidEmployeesCount,
            'transfersCount'        => $transfersCount,
            'companies'             => $companies,
        ]);
    }

    public function employeeBalanceList()
    {
        return Inertia::render('Admin/Libraries/EmployeeBalance/DetailList', [
            'title'     => __('general.employeeBalance'),
        ]);
    }

    public function employeeBalanceConfirm(Request $request)
    {
        if(Auth::user()->role == 'ADMINISTRATOR')
            return response()->json(['status'  => 'error', 'message' => __('general.not_added')], 500);

        try {
            DB::beginTransaction();

            if($request->employeeIds) {
                $groupData = collect($request->data)->groupBy('employee_id');

                foreach($request->employeeIds as $employeeId) {
                    if($groupData && isset($groupData[$employeeId])) {
                        foreach($groupData as $k => $v) {
                            $idsArr = $v->pluck('id');
                            $getTotalAmount = EmployeeBalanceList::whereIn('id', $idsArr)->where('is_paid', 0)->sum('amount');

                            if($getTotalAmount) {
                                $transfer = Transfer::create([
                                    'paying_user_id'   => Auth::user()->id,
                                    'paid_employee_id' => $k,
                                    'amount'           => $getTotalAmount,
                                    'note'             => $request->note
                                ]);

                                foreach($v as $info) {
                                    if(!TransferDetail::where('employee_balance_list_id', $info['id'])->first()) {
                                        TransferDetail::create([
                                            'transfer_id'              => $transfer->id,
                                            'employee_balance_list_id' => $info['id'],
                                        ]);

                                        EmployeeBalanceList::where('id', $info['id'])->update([
                                            'is_paid'         => 1,
                                            'paid_created_at' => date('Y-m-d H:i:s'),
                                        ]);

                                        $salaryProject = new SalaryProjectClass();
                                        $salaryCreatePaymentRegistry = $salaryProject->salaryCreatePaymentRegistryNew($employeeId, $info['id']);

                                        // EmployeeBalanceListTransfer::create([

                                        // ]);
                                    }
                                }
                            }
                        }
                    } else {
                        $getEmployeeBalanceList = EmployeeBalanceList::where('employee_id', $employeeId)->where('is_paid', 0)->get();
                        $getTotalAmount = EmployeeBalanceList::where('employee_id', $employeeId)->where('is_paid', 0)->sum('amount');

                        if($getTotalAmount) {
                            $transfer = Transfer::create([
                                'paying_user_id'   => Auth::user()->id,
                                'paid_employee_id' => $employeeId,
                                'amount'           => $getTotalAmount,
                                'note'             => $request->note
                            ]);

                            foreach($getEmployeeBalanceList as $info) {
                                TransferDetail::create([
                                    'transfer_id'              => $transfer->id,
                                    'employee_balance_list_id' => $info->id,
                                ]);

                                EmployeeBalanceList::where('id', $info->id)->update([
                                    'is_paid'         => 1,
                                    'paid_created_at' => date('Y-m-d H:i:s'),
                                ]);

                                $salaryProject = new SalaryProjectClass();
                                $salaryCreatePaymentRegistry = $salaryProject->salaryCreatePaymentRegistryNew($employeeId, $info['id']);
                            }
                        }
                    }
                }
            }

            DB::commit();
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json([
                'status'  => 'error',
                'message' => __('general.not_added')
            ], 500);
        }

        return response()->json([
            'status'  => 'success',
            'message' => __('general.successfully_added')
        ], 201);
    }
}
