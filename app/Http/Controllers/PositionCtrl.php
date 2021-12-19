<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Position;

class PositionCtrl extends Controller
{

    public function dt(Request $req, Position $position)
    {
        // build query
        $data = $position->newQuery();
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
        $data = $data->with('unit');
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

}
