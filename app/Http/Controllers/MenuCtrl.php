<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

use App\Menu;
use Spatie\Permission\Models\Permission;
use DB;

class MenuCtrl extends Controller
{

    public function get($parent, $id)
    {
        if(empty($id)) {
            if(!empty($parent))
                $p = Menu::find($parent);
            return response()->json([
                'id'            => 0,
                'parent_id'     => $parent,
                'name'          => '',
                'slug'          => empty($parent) ? '/' : $p->slug,
                'icon'          => '',
                'is_active'     => true,
                'order_no'      => empty($parent) ? (Menu::all()->count() +1) : ($p->children()->count() +1),
                'permissions'   => [],
                'arrPerms'      => Permission::all()
            ], Response::HTTP_OK);
        }

        $menu = Menu::find($id);
        $out = $menu->toArray();
        $out['permissions'] = $menu->permissions->pluck('name')->all();
        $out['arrPerms'] = Permission::all();
        return response()->json($out, Response::HTTP_OK);
    }

    public function tree()
    {
        $menu = Menu::where('parent_id', 0)
            ->orderBy('order_no')
            ->get();
        return response()->json($this->build_tree($menu), Response::HTTP_OK);
    }
    
    private function build_tree($data)
    {
        $op = [];
        foreach ($data as $value) {
            // array build of current item
            $item = [
                'label' => $value->name,
                'id'    => $value->id,
                'icon'  => $value->icon,
                'perms' => $value->permissions,
                'attrs' => [
                    'slug'      => $value->slug,
                    'is_active' => $value->is_active,
                ]
            ];
            // build children of current item
            $childrenData = $value->children()->get();
            if(!$childrenData->isEmpty()) {
                $children = $this->build_tree($childrenData);
                $item['children'] = $children;
            }
            // build item
            $op[] = $item;
        }
        return $op;
    }

    public function store(Request $req)
    {
        return DB::transaction(function() use($req) {
            try {
                // validations
                $validatorArr = [
                    'name'      => 'required',
                    'slug'      => 'required',
                    'order_no'  => 'required|numeric'
                ];
                if(!isset($req->id)) {
                    $validatorArr['slug'] = $validatorArr['slug'] .'|unique:menus';
                }
                $validator = Validator::make($req->all(), $validatorArr);
                if($validator->fails()) {
                    $errorMsg = "<br>> ". implode("<br>> ", $validator->errors()->all());
                    throw new \Exception($errorMsg);
                }

                // store manu
                $menu = Menu::updateOrCreate(
                    [ 'slug' => $req->slug ],
                    $req->only([
                        'parent_id', 'name', 'slug', 'icon', 'is_active', 'order_no'
                    ])
                );

                // sync permissions to menu
                if(!empty($req->permissions)) {
                    $perms = $req->permissions;
                    $arrPerms = [];
                    foreach ($perms as $perm) {
                        $p = Permission::firstOrCreate(
                            [ 'name' => $perm ],
                            [ 'guard_name' => 'web' ]
                        );
                        $arrPerms[] = $p;
                    }
                    $menu->syncPermissions($arrPerms);
                }

                return response($menu->jsonSerialize(), Response::HTTP_CREATED);
            } catch (\Exception $e) {
                DB::rollback();
                return response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    }

    public function destroy($id)
    {
        $menu = Menu::find($id);
        $this->do_destroy($menu);
        return response(null, Response::HTTP_OK);
    }

    private function do_destroy($data)
    {
        // get all decendant
        $children = $data->children()->get();
        if($children->isNotEmpty()) {
            foreach ($children as $menu) {
                // recursive delete
                $this->do_destroy($menu);
            }
        }

        // delete current
        $data->permissions()->delete();
        $data->delete();
    }

}
