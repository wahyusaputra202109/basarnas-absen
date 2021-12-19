<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SiagaAssign extends Model
{

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];

    protected $hidden = [ 'created_at', 'updated_at' ];

    protected $dates = [ 'tanggal', 'mulaimasuk', 'selesaimasuk', 'mulaipulang', 'selesaipulang' ];

    public function employee()
    {
        return $this->belongsTo('App\Employee', 'nip', 'nip');
    }

    public function shift()
    {
        return $this->belongsTo('App\SiagaShift', 'shift_id', 'id');
    }

    public function siaga_jabatan()
    {
        return $this->belongsTo('App\SiagaPosition', 'jabatan_id', 'id');
    }

}
