<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

use App\Role;
use App\Menu;
use Spatie\Permission\Models\Permission;
use DB;

class RoleCtrl extends Controller
{
    
    public function dt(Request $req, Role $role)
    {
        // build query
        $data = $role->newQuery();
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
        if(empty($id)) {
            return response()->json([
                'id'            => '',
                'name'          => '',
                'old_name'      => '',
                'guard_name'    => 'web',
                'menus'         => [],
                'permissions'   => [],
                'arrPerms'      => Permission::all()->pluck('id', 'name')->all(),
            ], Response::HTTP_OK);
        }

        $role = Role::find($id);
        $out = $role->toArray();
        $out['menus'] = Menu::role($role)->get()->pluck('id')->all();
        $out['permissions'] = $role->permissions->pluck('name')->all();
        $out['arrPerms'] = Permission::all()->pluck('id', 'name')->all();
        return response()->json($out, Response::HTTP_OK);
    }

    public function store(Request $req)
    {
        return DB::transaction(function() use($req) {
            try {
                // validations
                $validatorArr = [
                    'name'  => 'required'
                ];
                if(!isset($req->id)) {
                    $validatorArr['name'] = $validatorArr['name'] .'|unique:roles';
                }
                $validator = Validator::make($req->all(), $validatorArr);
                if($validator->fails()) {
                    $errorMsg = "<br>> ". implode("<br>> ", $validator->errors()->all());
                    throw new \Exception($errorMsg);
                }

                // store role
                $role = Role::updateOrCreate(
                    [ 'name' => $req->old_name ],
                    $req->only([ 'name', 'guard_name' ])
                );

                // sync menus to role
                $role->syncMenus($req->menus);

                // sync menus to role
                $role->syncPermissions($req->permissions);

                return response($role->jsonSerialize(), Response::HTTP_CREATED);
            } catch (\Exception $e) {
                DB::rollback();
                return response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    }

    public function destroy($id)
    {
        Role::destroy($id);
        return response(null, Response::HTTP_OK);
    }

}
