<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];

    protected $hidden = [ 'created_at', 'updated_at' ];
    
    protected $dates = [ 'holiday_date' ];

    protected $appends = [ 'date_frm' ];

    public function getDateFrmAttribute()
    {
        return $this->holiday_date->format('d/m/Y');
    }

}
