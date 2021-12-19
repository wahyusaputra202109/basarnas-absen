<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkUnit extends Model
{
    
    public function positions()
    {
        return $this->hasMany('App\Position', 'unit_id', 'id');
    }

    public function employees()
    {
        return $this->hasManyThrough('App\Employee', 'App\Position', 'unit_id', 'position_id', 'id', 'id');
    }

    public function users()
    {
        return $this->belongsToMany('App\User', 'users_has_work_units', 'unit_id', 'user_id');
    }

    public function parents()
    {
        return $this->hasMany('App\WorkUnit', 'parent_id', 'id');
    }

    public function employeesByParent()
    {
        $parents = $this->parents()->get()->pluck('id')->all();
        return \DB::table('employees as e')
            ->select('e.*', 'p.name as jabatan', 'w.name as unit')
            ->join('positions as p', 'p.id', '=', 'e.position_id')
            ->join('work_units as w', 'w.id', '=', 'p.unit_id')
            ->whereIn('w.parent_id', $parents)
            ->orderBy('p.order_id', 'ASC')
            ->orderBy('p.id', 'ASC')
            ->orderBy('e.id', 'ASC');
    }

    public function genReport($from, $to)
    {
        return \DB::table('work_units AS w')
            ->select('e.nip', 'a.submitted_at', 'a.workfrom')
            ->join('positions AS p', 'p.unit_id', '=', 'w.id')
            ->join('employees AS e', 'e.position_id', '=', 'p.id')
            ->join('absents AS a', 'a.nip', '=', 'e.nip')
            ->where([
                [ 'w.id', '=', $this->id ],
                [ 'a.submitted_at', '>=', $from ],
                [ 'a.submitted_at', '<=', $to ]
            ])
            ->orderBy('p.order_id', 'ASC')
            ->orderBy('p.id', 'ASC')
            ->orderBy('a.submitted_at', 'ASC');
    }

}
