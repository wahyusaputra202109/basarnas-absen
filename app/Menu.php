<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Spatie\Permission\Traits\HasRoles;

class Menu extends Model
{

    use HasRoles;

    protected $guard_name = 'web';
    
    protected $fillable = [ 'parent_id', 'name', 'slug', 'icon', 'is_active', 'order_no' ];

    protected $casts = [ 'is_active' => 'boolean' ];

    protected $hidden = [ 'created_at', 'updated_at' ];

    public function children()
    {
        return $this->hasMany('App\Menu', 'parent_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo('App\Menu', 'parent_id', 'id');
    }

    public static function tree($data)
    {
        $lists = $data->pluck('id')->all();
        foreach ($data as $val) {
            if($val->parent_id > 0 AND !in_array($val->parent_id, $lists)) {
                $lists[] = $val->parent()->first()->id;
            }
        }
        // return (new static)::whereIn('id', $lists)->where('is_active', 1)->orderBy('order_no')->get();
        $menus = Menu::whereIn('id', $lists)->where('is_active', 1)->orderBy('order_no')->get();
        return (new Menu)->buildMenus($menus);
    }

    private function buildMenus($data, $parent = 0)
    {
        $op = [];
        foreach ($data as $value) {
            if($value->parent_id===$parent) {
                // array build of current item
                $item = [
                    'id'    => $value->id,
                    'name'  => $value->name,
                    'url'   => $value->slug,
                    'icon'  => $value->icon,
                ];
                // build children of current item
                $children = $this->buildMenus($data, $value->id);
                if($children)
                    $item['children'] = $children;
                // build item
                $op[] = $item;
            }
        }
        return $op;
    }
    
}
