<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AbsentDup extends Model
{

    protected $table = 'absents_dup';
    
    protected $guarded = [ 'id', 'created_at', 'updated_at' ];

    protected $hidden = [ 'created_at', 'updated_at' ];

    protected $dates = [ 'submitted_at' ];

}
