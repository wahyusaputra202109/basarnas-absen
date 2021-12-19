<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\WorkUnit;
use App\Employee;

class WorkUnitCtrl extends Controller
{
    
    public function dt()
    {
        $units = WorkUnit::all();
        return response($units->jsonSerialize(), Response::HTTP_OK);
    }

    public function getUnitsByUserId()
    {
        $user = auth()->user();
        $units = $user->work_units;
        if($user->hasRole('administrator')) {
            $units->prepend([
                'id'            => 0,
                'name'          => '-- All Work Units --',
                'parent_id'     => 0,
                'created_at'    => null,
                'updated_at'    => null,
            ]);
        }
        return response($units->jsonSerialize(), Response::HTTP_OK);
    }

    public function getEmployees($id)
    {
        $unit = WorkUnit::find($id);
        $emps = $unit->employees()->get();

        return response($emps->jsonSerialize(), Response::HTTP_OK);
    }

}
