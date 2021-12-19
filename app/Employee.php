<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];

    protected $hidden = [ 'created_at', 'updated_at' ];

    protected $dates = [ 'level_date', 'position_date' ];

    public function position()
    {
        return $this->belongsTo('App\Position', 'position_id', 'id');
    }

    public function level()
    {
        return $this->belongsTo('App\Level', 'level_id', 'id');
    }

}
