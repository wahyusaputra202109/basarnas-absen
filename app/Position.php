<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    
    protected $guarded = [ 'id', 'created_at', 'updated_at' ];

    protected $hidden = [ 'created_at', 'updated_at' ];

    public function unit()
    {
        return $this->belongsTo('App\WorkUnit', 'unit_id', 'id');
    }

    public function employees()
    {
        return $this->hasMany('App\Employee', 'position_id', 'id');
    }

}
