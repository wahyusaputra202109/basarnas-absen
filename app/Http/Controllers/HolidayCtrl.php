<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

use App\Holiday;
use DB;

class HolidayCtrl extends Controller
{

    public function dt(Request $req, Holiday $holiday)
    {
        // build query
        $data = $holiday->newQuery();
        // apply filter
        if(!empty($req->search)) {
            $data->where('description', 'LIKE', '%'. $req->search .'%');
        }
        // get count before limit
        $count = $data->count();
        // apply order
        if(isset($req->sort)) {
            $sorts = $req->sort;
            foreach ($sorts as $sort) {
                $val = explode('|', $sort);
                // below script only happen for Holiday only
                $data = $data->orderBy(($val[0] == 'date_frm' ? 'holiday_date' : $val[0]), $val[1]);
            }
        }
        // apply limit
        $data = $data->offset(($req->page -1) *$req->viewData)->limit($req->viewData);
        // real data
        $data = $data->get();

        // build result
        $out = [
            'data'      => $data->toArray(),
            'from'      => ($req->page -1) *$req->viewData +1,
            'to'        => $data->count(),
            'totalData' => $count,
            'totalPage' => ceil($count /$req->viewData)
        ];
        return response()->json($out, Response::HTTP_OK);
    }

    public function get($id)
    {
        if(empty($id)) {
            return response()->json([
                'id'            => '',
                'holiday_date'  => '',
                'description'   => '',
            ], Response::HTTP_OK);
        }

        $holiday = Holiday::find($id);
        $out = $holiday->toArray();
        return response()->json($out, Response::HTTP_OK);
    }

    public function store(Request $req)
    {
        return DB::transaction(function() use($req) {
            try {
                // validations
                $validatorArr = [
                    'holiday_date'  => 'required'
                ];
                if(!isset($req->id)) {
                    $validatorArr['holiday_date'] = $validatorArr['holiday_date'] .'|unique:holidays';
                }
                $validator = Validator::make($req->all(), $validatorArr);
                if($validator->fails()) {
                    $errorMsg = "<br>> ". implode("<br>> ", $validator->errors()->all());
                    throw new \Exception($errorMsg);
                }

                // store holiday
                $holiday = Holiday::updateOrCreate(
                    [ 'holiday_date' => $req->holiday_date ],
                    $req->only([ 'holiday_date', 'description' ])
                );

                return response($holiday->jsonSerialize(), Response::HTTP_CREATED);
            } catch (\Exception $e) {
                DB::rollback();
                return response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    }

    public function destroy($id)
    {
        Holiday::destroy($id);
        return response(null, Response::HTTP_OK);
    }

}
