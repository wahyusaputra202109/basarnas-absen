<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{

    use Notifiable, HasApiTokens, HasRoles;

    protected $fillable = [
        'name', 'username', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'api_token', 'remember_token', 'created_at', 'updated_at'
    ];

    protected $dates = [ 'expired_at' ];

    protected $casts = [ 'is_active' => 'boolean' ];

    public function work_units()
    {
        return $this->belongsToMany('App\WorkUnit', 'users_has_work_units', 'user_id', 'unit_id');
    }

}
