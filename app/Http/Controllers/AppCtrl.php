<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Menu;

class AppCtrl extends Controller
{
    
    public function index()
    {
        return view('app');
    }

    public function login(Request $req)
    {
        $out = [
            'result'    => 'error',
            'message'   => 'Invalid credentials!',
        ];
        if(auth()->attempt([ 'username' => $req->username, 'password' => $req->password ])) {
            $out['result'] = 'success';
            $out['message'] = 'Successfully logged in.';
    
            // auth data
            $user = auth()->user();
            $user->tokens()->delete();
            $out['values']['user'] = $user;
            $out['values']['token'] = $user->createToken(env('APP_NAME', 'adesr-apps') .'-token')->accessToken;
            $role = $user->roles->first();
            $menus = Menu::role($role)->get();
            $out['values']['menus'] = Menu::tree($menus);
            $out['values']['permissions'] = $user->getAllPermissions()->pluck('name')->all();
        }
    
        return response()->json($out);
    }

    public function token(Request $req)
    {
        $out = [
            'result'    => 'error',
            'message'   => 'Invalid credentials!',
        ];
        if(auth()->attempt([ 'username' => $req->username, 'password' => $req->password ])) {
            $out['result'] = 'success';
            $out['message'] = 'Successfully logged in.';
    
            // auth data
            $user = auth()->user();
            $user->tokens()->delete();
            $out['values']['token'] = $user->createToken(env('APP_NAME', 'adesr-apps') .'-token')->accessToken;
        }
    
        return response()->json($out);
    }

}
