<?php

namespace App\Http\Controllers\Admin;

use DB;
use Auth;
use Log;
use Storage;
use App\Traits\UploadFile;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Admin\AdminEstablishmentRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Establishment,
    App\Models\DriverLicense,
    App\Models\MeanOfTransport,
    App\Models\Position,
    App\Models\User;

class EstablishmentController extends Controller
{
    use UploadFile;

    private $IMAGE_FOLDER_PATH = 'images/establishments';

    public function index(Request $request)
    {
        return Inertia::render('Admin/Libraries/Establishments/List', [
            'title'            => __('general.establishment'),
            'driverLicenses'   => DriverLicense::select('id', 'name->'. app()->getLocale(). ' as name_d')->get(),
            'meanOfTransports' => MeanOfTransport::select('id', 'name->'. app()->getLocale(). ' as name_m')->get(),
            'companies'        => User::select('id', 'company_name')->where('role', 'COMPANY')->get(),
            'positions'        => Position::select('id', 'name->'. app()->getLocale(). ' as name_p')->get(),
            'establishments'   => Establishment::select(
                'id',
                'user_id',
                'admin_user_id',
                'company_id',
                'status',
                'admin_status',
                'gender',
                'name->'. app()->getLocale(). ' as name_e',
                'obligation->'. app()->getLocale(). ' as obligation_e',
                'requirement->'. app()->getLocale(). ' as requirement_e',
                'absence_disability',
                'salary',
                'tax',
                'commission',
                'img',
                'admin_status_updated_at',
            )
            ->with([
                'driverLicenseLists',
                'meanOfTransportLists',
                'positions',
                'company',
                'user' => function($query) {
                    $query->select('id', 'role', 'email', 'name', 'company_name');
                },
                'adminUser' => function($query) {
                    $query->select('id', 'email', 'name');
                }
            ])
            ->when($request->text, function ($q) use ($request) {
                return $q->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->text . '%')
                             ->orWhere('obligation', 'like', '%' . $request->text . '%')
                             ->orWhere('requirement', 'like', '%' . $request->text . '%');
                });

            })
            ->when($request->companies, function ($q) use ($request) {
                return $q->where(function ($q) use ($request) {
                    $q->whereIn('company_id', $request->companies);
                });
            })
            ->when($request->statuses, function ($q) use ($request) {
                return $q->where(function ($q) use ($request) {
                    $q->whereIn('status', $request->statuses);
                });
            })
            ->when($request->statuses, function ($q) use ($request) {
                return $q->where(function ($q) use ($request) {
                    $q->whereIn('status', $request->statuses);
                });
            })
            ->where(function($q) {
                $q->where('status', '!=', 'DRAFT')
                  ->orWhere(function($q) {
                    $q->whereHas('user', function (Builder $query) {
                        $query->where('role', 'ADMIN');
                    });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(50)
            ->appends(request()->query()),
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        if(Auth::user()->role == 'ADMINISTRATOR')
            return response()->json(['message' => __('general.not_added')], 500);

        try {
            $establishment = Establishment::create([
                'user_id'                 => Auth::user()->id,
                'admin_user_id'           => Auth::user()->id,
                'company_id'              => $request->company_id,
                'gender'                  => $request->gender,
                'status'                  => $request->status,
                'admin_status'            => $request->status,
                'name'                    => $request->name,
                'obligation'              => $request->obligation,
                'requirement'             => $request->requirement,
                'salary'                  => $request->salary,
                'tax'                     => $request->tax,
                'commission'              => $request->commission,
                'absence_disability'      => $request->absence_disability ? 1 : 0,
                'img'                     => $this->uploadFile($request->img, $this->IMAGE_FOLDER_PATH),
                'admin_status_updated_at' => date('Y-m-d H:i:s')
            ]);

            $establishment->driverLicenses()->sync($request->driverLicenses);
            $establishment->meanOfTransports()->sync($request->meanOfTransports);
            $establishment->positions()->sync($request->positions);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message' => __('general.not_added')], 500);
        }

        return response()->json(['message' => __('general.successfully_added')], 201);
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(AdminEstablishmentRequest $request, Establishment $establishment)
    {
        if(Auth::user()->role == 'ADMINISTRATOR')
            return response()->json(['message' => __('general.not_edited')], 500);

        $establishment->company_id         = $request->company_id;
        $establishment->gender             = $request->gender;
        $establishment->name               = $request->name;
        $establishment->obligation         = $request->obligation;
        $establishment->requirement        = $request->requirement;
        $establishment->salary             = $request->salary;
        $establishment->tax                = $request->tax;
        $establishment->commission         = $request->commission;
        $establishment->absence_disability = $request->absence_disability ? 1 : 0;

        $establishment->driverLicenses()->sync($request->driverLicenses);
        $establishment->meanOfTransports()->sync($request->meanOfTransports);
        $establishment->positions()->sync($request->positions);

        if($request->status) {
            if(($request->status == 'CONFIRMED' || $request->status == 'PASSIVATED' || $request->status == 'REJECTED') && $request->status != $establishment->status) {
                $establishment->admin_user_id           = Auth::user()->id;
                $establishment->admin_status            = $request->status;
                $establishment->admin_status_updated_at = date('Y-m-d H:i:s');
            }

            $establishment->status = $request->status;
        }


        if(($request->img && $request->hasFile('img')) || $request->delete_img == 'true') {
            if($establishment->img && Storage::disk('public')->exists($establishment->img))
                Storage::disk('public')->delete($establishment->img);

            $establishment->img = $this->uploadFile($request->img, $this->IMAGE_FOLDER_PATH);
        }

        return $establishment->save()
            ? response()->json(['message' => __('general.successfully_edited')], 201)
            : response()->json(['message' => __('general.not_edited')], 500);
    }

    public function destroy($id)
    {
        //
    }

    public function setStatus(Request $request) {
        if(Auth::user()->role == 'ADMINISTRATOR')
            return response()->json(['message' => __('general.not_edited')], 500);

        $establishment             = Establishment::where('id', $request->id)->first();
        $establishment->name       = $request->name;
        $establishment->salary     = $request->salary;
        $establishment->tax        = $request->tax;
        $establishment->commission = $request->commission;
        $establishment->positions()->sync($request->positions);

        if($request->status)
            $establishment->status     = $request->status;

        return $establishment->save()
            ? response()->json(['message' => __('general.successfully_edited')], 201)
            : response()->json(['message' => __('general.not_edited')], 500);
    }
}
