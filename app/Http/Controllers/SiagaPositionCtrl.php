<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

use App\SiagaPosition;
use DB;

class SiagaPositionCtrl extends Controller
{

    public function dt(Request $req, SiagaPosition $assign)
    {
        // build query
        $data = $assign->newQuery();
        // apply filter
        if(!empty($req->search)) {
            $data->where('name', 'LIKE', '%'. $req->search .'%');
        }
        // get count before limit
        $count = $data->count();
        // apply order
        if(isset($req->sort)) {
            $sorts = $req->sort;
            foreach ($sorts as $sort) {
                $val = explode('|', $sort);
                $data = $data->orderBy($val[0], $val[1]);
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
        $assign = SiagaPosition::find($id);

        return response($assign->jsonSerialize(), Response::HTTP_OK);
    }

    public function store(Request $req)
    {
        return DB::transaction(function() use($req) {
            try {
                // validations
                $validatorArr = [
                    'name'          => 'required',
                ];
                $validator = Validator::make($req->all(), $validatorArr);
                if($validator->fails()) {
                    $errorMsg = "> ". implode("\n> ", $validator->errors()->all());
                    throw new \Exception($errorMsg);
                }

                // store user
                $position = SiagaPosition::findOrNew($req->id);
                $position->name = $req->name;
                $position->save();

                return response($position->jsonSerialize(), Response::HTTP_CREATED);
            } catch (\Exception $e) {
                DB::rollback();
                return response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    }

    public function destroy($id)
    {
        SiagaPosition::destroy($id);
        return response(null, Response::HTTP_OK);
    }

}
