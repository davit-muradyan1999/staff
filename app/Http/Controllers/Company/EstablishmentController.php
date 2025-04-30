<?php

namespace App\Http\Controllers\Company;

use Auth;
use Log;
use Storage;
use App\Traits\UploadFile;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Establishment,
    App\Models\DriverLicense,
    App\Models\Position,
    App\Models\MeanOfTransport;

class EstablishmentController extends Controller
{
    use UploadFile;

    private $IMAGE_FOLDER_PATH = 'images/establishments';

    public function establishment(Request $request) {
        $companyId = Auth::user()->parent_user_id ? Auth::user()->parent_user_id : Auth::user()->id;

        return Inertia::render('Company/Establishment', [
            'title'            => __('general.establishment'),
            'driverLicenses'   => DriverLicense::select('id', 'name->'. app()->getLocale(). ' as name_d')->get(),
            'meanOfTransports' => MeanOfTransport::select('id', 'name->'. app()->getLocale(). ' as name_m')->get(),
            'positions'        => Position::select('id', 'name->'. app()->getLocale(). ' as name_p')->get(),
            'establishments'   => Establishment::select(
                'id',
                'status',
                'user_id',
                'gender',
                'name->'. app()->getLocale(). ' as name_e',
                'obligation->'. app()->getLocale(). ' as obligation_e',
                'requirement->'. app()->getLocale(). ' as requirement_e',
                'absence_disability',
                'salary',
                'tax',
                'commission',
                'img',
            )
            ->with([
                'driverLicenseLists',
                'meanOfTransportLists',
                'positions',
                'user' => function($query) {
                    $query->select('id', 'email', 'name');
                },
            ])
            ->when($request->text, function ($q) use ($request) {
                return $q->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->text . '%')
                             ->orWhere('obligation', 'like', '%' . $request->text . '%')
                             ->orWhere('requirement', 'like', '%' . $request->text . '%');
                });

            })
            ->when($request->statuses, function ($q) use ($request) {
                return $q->where(function ($q) use ($request) {
                    $q->whereIn('status', $request->statuses);
                });
            })
            ->where(function($q) {
                $q->where(function($q) {
                    $q->whereHas('user', function (Builder $query) {
                        $query->whereIn('role', ['COMPANY', 'MODERATOR']);
                    });
                })
                ->orWhere(function($q) {
                    $q->where('status', '!=', 'DRAFT')->whereHas('user', function (Builder $query) {
                        $query->where('role', 'ADMIN');
                    });
                });
            })
            ->where('company_id', $companyId)
            ->paginate(50)
            ->appends(request()->query()),
        ]);
    }

    public function addEstablishment(Request $request) {
        try {
            if(Auth::user()->role == 'COMPANY' && !Auth::user()->company_name)
                return response()->json(['message' => __('general.not_added')], 500);

            $companyId = Auth::user()->parent_user_id ? Auth::user()->parent_user_id : Auth::user()->id;

            $establishment = Establishment::create([
                'user_id'            => Auth::user()->id,
                'company_id'         => $companyId,
                'gender'             => $request->gender,
                'status'             => $request->status,
                'name'               => $request->name,
                'obligation'         => $request->obligation,
                'requirement'        => $request->requirement,
                'salary'             => $request->salary,
                'absence_disability' => $request->absence_disability ? 1 : 0,
                'img'                => $this->uploadFile($request->img, $this->IMAGE_FOLDER_PATH)
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

    public function updateEstablishment(Request $request) {
        try {
            $establishment = Establishment::where('id', $request->id)->first();

            if($establishment->status == 'SENDED' || $establishment->status == 'CONFIRMED')
                return response()->json(['message' => __('general.not_edited')], 500);

            $data = [
                'gender'             => $request->gender,
                'name'               => $request->name,
                'obligation'         => $request->obligation,
                'requirement'        => $request->requirement,
                'salary'             => $request->salary,
                'absence_disability' => $request->absence_disability ? 1 : 0,
            ];

            if($request->status != 'SAVE') {
                $data['status']  = $request->status;
            }

            if(($request->img && $request->hasFile('img')) || $request->delete_img == 'true') {
                if($establishment->img && Storage::disk('public')->exists($establishment->img))
                    Storage::disk('public')->delete($establishment->img);

                $data['img'] = $this->uploadFile($request->img, $this->IMAGE_FOLDER_PATH);
            }

            $establishment->driverLicenses()->sync($request->driverLicenses);
            $establishment->meanOfTransports()->sync($request->meanOfTransports);
            $establishment->positions()->sync($request->positions);

            $establishment->update($data);
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message' => __('general.not_edited')], 500);
        }

        return response()->json(['message' => __('general.successfully_edited')], 201);
    }

    public function deleteEstablishment(Request $request) {
        try {
            $establishment = Establishment::where('id', $request->id)->first();

            if($establishment->img && Storage::disk('public')->exists($establishment->img))
                Storage::disk('public')->delete($establishment->img);

            $establishment->delete();
        } catch(\Exception $e) {
            Log::info($e);
            return response()->json(['message' => __('general.not_deleted')], 500);
        }

        return response()->json(['message' => __('general.successfully_deleted')], 201);
    }

    public function getEstablishmentById($id)
    {
        return response()->json([
            'status'        => 'success',
            'establishment' => Establishment::select(
                'id',
                'status',
                'gender',
                'name->'. app()->getLocale(). ' as name_e',
                'obligation->'. app()->getLocale(). ' as obligation_e',
                'requirement->'. app()->getLocale(). ' as requirement_e',
                'absence_disability',
                'salary',
                'tax',
                'commission',
            )
            ->with([
                'driverLicenseLists' => function($q) {
                    $q->with([
                        'driverLicense' => function($q) {
                            $q->select('id',  'name->'. app()->getLocale(). ' as name_d');
                        }
                    ]);
                },
                'meanOfTransportLists' => function($q) {
                    $q->with([
                        'meanOfTransport' => function($q) {
                            $q->select('id',  'name->'. app()->getLocale(). ' as name_e');
                        }
                    ]);
                },
            ])
            ->find($id)
        ]);
    }

    public function getEstablishmentsByCompanyId($company_id) {
        return response()->json([
            'status'         => 'success',
            'establishments' => Establishment::select('id', 'name->'. app()->getLocale(). ' as name_e')->where('company_id', $company_id)->where('status', 'CONFIRMED')->get()
        ]);
    }
}
