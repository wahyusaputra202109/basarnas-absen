<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

use App\Employee;
use App\Level;
use App\Position;
use DB;

class EmployeeCtrl extends Controller
{

    public function dt(Request $req, Employee $emp)
    {
        // build query
        $data = $emp->newQuery();
        // apply filter
        if(!empty($req->search)) {
            $data->where('nip', 'LIKE', '%'. $req->search .'%')
            ->orWhere('name', 'LIKE', '%'. $req->search .'%');
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
        $data = $data->with('position');
        $data = $data->with('level');
        $data = $data->get();

        // build result
        $out = [
            'data'      => $data->toArray(),
            'from'      => ($req->page -1) *$req->viewData +1,
            'to'        => $data->count(),
            'totalData' => $count,
            'totalPage' => ceil($count /$req->viewData),
        ];
        return response()->json($out, Response::HTTP_OK);
    }

    public function get($id)
    {
        $data = Employee::with('position')
            ->with('position.unit')
            ->with('level')
            ->where('id', $id)
            ->first();

        return response($data->jsonSerialize(), Response::HTTP_OK);
    }

    public function store(Request $req)
    {
        return DB::transaction(function() use($req) {
            try {
                // validations
                $validatorArr = [
                    'nip'           => 'required',
                    'name'          => 'required',
                    'position_id'   => 'required',
                    'level_id'      => 'required',
                ];
                $validator = Validator::make($req->all(), $validatorArr);
                if($validator->fails()) {
                    $errorMsg = "> ". implode("\n> ", $validator->errors()->all());
                    throw new \Exception($errorMsg);
                }

                // create new level if id is not numeric
                if(!is_numeric($req->level_id)) {
                    $level = Level::create([ 'name'  => $req->level_id ]);
                    $req->merge([ 'level_id' => $level->id ]);
                }
                if(!is_numeric($req->position_id)) {
                    $position = Position::create([ 'name'  => $req->position_id, 'unit_id' => $req->unit_id ]);
                    $req->merge([ 'position_id' => $position->id ]);
                }

                // store user
                $emp = Employee::findOrNew($req->id);
                $emp->nip = $req->nip;
                $emp->name = $req->name;
                $emp->level_id = $req->level_id;
                $emp->position_id = $req->position_id;
                if(empty($req->id)) {
                    $emp->level_date = date('Y-m-d');
                    $emp->position_date = date('Y-m-d');
                }
                $emp->save();

                return response($emp->jsonSerialize(), Response::HTTP_CREATED);
            } catch (\Exception $e) {
                DB::rollback();
                return response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    }

    public function destroy($id)
    {
        Employee::destroy($id);
    }

}
