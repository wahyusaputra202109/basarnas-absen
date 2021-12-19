<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{

    protected $guard_name = 'web';

    protected $hidden = [ 'created_at', 'updated_at' ];
    
    public function menus()
    {
        return $this->morphedByMany(
            'App\Menu',
            'model',
            config('permission.table_names.model_has_roles'),
            'role_id',
            config('permission.column_names.model_morph_key')
        );
    }
    
    public function syncMenus($menus)
    {
        $this->menus()->detach();
        $id = $this;
        collect($menus)->each(function($val, $key) use($id) {
            Menu::find($val)->assignRole($id);
        });
    }

}
