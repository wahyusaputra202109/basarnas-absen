<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

use App\User;
use App\Role;
use DB;
use Hash;

class UserCtrl extends Controller
{
    
    public function dt(Request $req, User $user)
    {
        // build query
        $data = $user->newQuery();
        // apply filter
        if(!empty($req->search)) {
            $data->where('name', 'LIKE', '%'. $req->search .'%')
                ->orWhere('username', 'LIKE', '%'. $req->search .'%')
                ->orWhere('email', 'LIKE', '%'. $req->search .'%');
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
        if(empty($id)) {
            return response()->json([
                'id'            => '',
                'name'          => '',
                'username'      => '',
                'email'         => '',
                'role'          => '',
                'arrRoles'      => Role::all(),
                'units'         => []
            ], Response::HTTP_OK);
        }

        $user = User::find($id);
        $out = $user->toArray();
        $out['role'] = $user->roles->pluck('id')->first();
        $out['arrRoles'] = Role::all();
        $out['units'] = $user->work_units->pluck('id')->all();
        return response()->json($out, Response::HTTP_OK);
    }

    public function store(Request $req)
    {
        return DB::transaction(function() use($req) {
            try {
                // validations
                $validatorArr = [
                    'name'      => 'required',
                    'username'  => 'required',
                    'email'     => 'required'
                ];
                if(!isset($req->id)) {
                    $validatorArr['username'] = $validatorArr['username'] .'|unique:users';
                }
                $validator = Validator::make($req->all(), $validatorArr);
                if($validator->fails()) {
                    $errorMsg = "<br>> ". implode("<br>> ", $validator->errors()->all());
                    throw new \Exception($errorMsg);
                }

                // store user
                $reqArr = [ 'name', 'username', 'email' ];
                if(empty($req->id)) {
                    $req->merge([ 'password' => Hash::make($req->username) ]);
                    $reqArr[] = 'password';
                }
                $user = User::updateOrCreate(
                    [ 'username' => $req->username ],
                    $req->only($reqArr)
                );

                // sync user to role
                $user->syncRoles($req->role);

                // sync user to work unit
                if(!empty($req->units))
                    $user->work_units()->sync($req->units);

                return response($user->jsonSerialize(), Response::HTTP_CREATED);
            } catch (\Exception $e) {
                DB::rollback();
                return response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    }

    public function destroy($id)
    {
        User::destroy($id);
        return response(null, Response::HTTP_OK);
    }

    public function confirm(Request $req)
    {
        $dbPassword = auth()->user()->password;
        if(Hash::check($req->password, $dbPassword)) {
            return response(null, Response::HTTP_OK);
        }
        return response('Password invalid!', Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function password(Request $req)
    {
        return DB::transaction(function() use($req) {
            try {
                $user = auth()->user();

                User::where('id', $user->id)
                    ->update([
                        'password'  => Hash::make($req->passwd)
                    ]);

                return response($user->jsonSerialize(), Response::HTTP_CREATED);
            } catch (\Exception $e) {
                DB::rollback();
                return response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    }

}
