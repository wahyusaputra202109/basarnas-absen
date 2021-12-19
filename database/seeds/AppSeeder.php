<?php

use Illuminate\Database\Seeder;

use App\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Menu;

class AppSeeder extends Seeder
{
    
    public function run()
    {
        // reset spatie cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // assign User: Administrator
        $user = User::create([
            'name'      => 'Administrator',
            'username'  => 'admin',
            'password'  => Hash::make('admin'),
            'email'     => 'as.ramdan13@gmail.com'
        ]);

        // assign Role: Administrator
        $role = Role::create([
            'name' => 'administrator'
        ]);
        $user->syncRoles([ $role ]);

        // assign Menus
        $menu = Menu::create([
            'name' => 'Home',
            'slug' => '/admin/home',
            'icon' => 'el-icon-s-home',
            'order_no' => 1
        ]);
        $menu->assignRole($role);
        $perm = Permission::create([ 'name' => 'change password' ]);
        $menu->givePermissionTo($perm);
        $role->givePermissionTo($perm);

        $menu = Menu::create([
            'name' => 'Setting',
            'slug' => '/admin/settings',
            'icon' => 'el-icon-s-tools',
            'order_no' => 99
        ]);
        $menu->assignRole($role);
        $setting_id = $menu->id;

        $menu = Menu::create([
            'parent_id' => $setting_id,
            'name' => 'Menu',
            'slug' => '/admin/menus',
            'icon' => 'el-icon-menu',
            'order_no' => 1
        ]);
        $menu->assignRole($role);
        $perm = Permission::create([ 'name' => 'index menu' ]);
        $menu->givePermissionTo($perm);
        $role->givePermissionTo($perm);
        $perm = Permission::create([ 'name' => 'create menu' ]);
        $menu->givePermissionTo($perm);
        $role->givePermissionTo($perm);
        $perm = Permission::create([ 'name' => 'update menu' ]);
        $menu->givePermissionTo($perm);
        $role->givePermissionTo($perm);
        $perm = Permission::create([ 'name' => 'delete menu' ]);
        $menu->givePermissionTo($perm);
        $role->givePermissionTo($perm);

        $menu = Menu::create([
            'parent_id' => $setting_id,
            'name' => 'Role',
            'slug' => '/admin/roles',
            'icon' => 'el-icon-help',
            'order_no' => 2
        ]);
        $menu->assignRole($role);
        $perm = Permission::create([ 'name' => 'index role' ]);
        $menu->givePermissionTo($perm);
        $role->givePermissionTo($perm);
        $perm = Permission::create([ 'name' => 'create role' ]);
        $menu->givePermissionTo($perm);
        $role->givePermissionTo($perm);
        $perm = Permission::create([ 'name' => 'update role' ]);
        $menu->givePermissionTo($perm);
        $role->givePermissionTo($perm);
        $perm = Permission::create([ 'name' => 'delete role' ]);
        $menu->givePermissionTo($perm);
        $role->givePermissionTo($perm);

        $menu = Menu::create([
            'parent_id' => $setting_id,
            'name' => 'User',
            'slug' => '/admin/users',
            'icon' => 'el-icon-s-custom',
            'order_no' => 3
        ]);
        $menu->assignRole($role);
        $perm = Permission::create([ 'name' => 'index user' ]);
        $menu->givePermissionTo($perm);
        $role->givePermissionTo($perm);
        $perm = Permission::create([ 'name' => 'create user' ]);
        $menu->givePermissionTo($perm);
        $role->givePermissionTo($perm);
        $perm = Permission::create([ 'name' => 'update user' ]);
        $menu->givePermissionTo($perm);
        $role->givePermissionTo($perm);
        $perm = Permission::create([ 'name' => 'delete user' ]);
        $menu->givePermissionTo($perm);
        $role->givePermissionTo($perm);

        $menu = Menu::create([
            'name' => 'Logout',
            'slug' => '/logout',
            'icon' => 'el-icon-switch-button',
            'order_no' => 100
        ]);
        $menu->assignRole($role);
        $perm = Permission::create([ 'name' => 'logout' ]);
        $menu->givePermissionTo($perm);
        $role->givePermissionTo($perm);
    }
    
}
