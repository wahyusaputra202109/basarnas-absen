<?php

Route::post('login', 'AppCtrl@login');
Route::post('token', 'AppCtrl@token');

Route::prefix('absen')->group(function() {
    Route::get('/time/{tz?}', 'AbsenCtrl@time');
    Route::post('/store', 'AbsenCtrl@store');
    Route::post('/siaga/store', 'SiagaAbsentCtrl@store');
});

Route::get('siaga/shift/dt', [ 'uses' => 'SiagaShiftCtrl@dt'/*, 'middleware' => 'permission:index shift'*/ ]);

Route::middleware([ 'auth:api' ])->group(function() {
    Route::prefix('menus')->group(function() {
        Route::get('/', [ 'uses' => 'MenuCtrl@tree', 'middleware' => 'permission:index menu' ]);
        Route::post('store', [ 'uses' => 'MenuCtrl@store', 'middleware' => 'permission:create menu|update menu' ]);
        Route::get('delete/{id}', [ 'uses' => 'MenuCtrl@destroy', 'middleware' => 'permission:delete menu' ]);
        Route::get('{parent}/{id}', [ 'uses' => 'MenuCtrl@get', 'middleware' => 'permission:index menu' ]);
    });
    Route::prefix('roles')->group(function() {
        Route::get('dt', [ 'uses' => 'RoleCtrl@dt', 'middleware' => 'permission:index role' ]);
        Route::post('store', [ 'uses' => 'RoleCtrl@store', 'middleware' => 'permission:create role|update role' ]);
        Route::get('delete/{id}', [ 'uses' => 'RoleCtrl@destroy', 'middleware' => 'permission:delete role' ]);
        Route::get('{id}', [ 'uses' => 'RoleCtrl@get', 'middleware' => 'permission:index role' ]);
    });
    Route::prefix('users')->group(function() {
        Route::get('dt', [ 'uses' => 'UserCtrl@dt', 'middleware' => 'permission:index user' ]);
        Route::post('store', [ 'uses' => 'UserCtrl@store', 'middleware' => 'permission:create user|update user' ]);
        Route::get('delete/{id}', [ 'uses' => 'UserCtrl@destroy', 'middleware' => 'permission:delete user' ]);
        Route::post('confirm', [ 'uses' => 'UserCtrl@confirm', 'middleware' => 'permission:change password' ]);
        Route::post('password', [ 'uses' => 'UserCtrl@password', 'middleware' => 'permission:change password' ]);
        Route::get('{id}', [ 'uses' => 'UserCtrl@get', 'middleware' => 'permission:index user' ]);
    });
    Route::prefix('work-units')->group(function() {
        Route::get('dt', [ 'uses' => 'WorkUnitCtrl@dt', 'middleware' => 'permission:change password' ]);
        Route::get('ls', [ 'uses' => 'WorkUnitCtrl@getUnitsByUserId', 'middleware' => 'permission:change password' ]);
        Route::get('{id}/employees', [ 'uses' => 'WorkUnitCtrl@getEmployees', 'middleware' => 'permission:change password' ]);
    });
    Route::prefix('absen')->group(function() {
        // Route::get('xls-per-unit', [ 'uses' => 'AbsenCtrl@xls_per_unit', 'middleware' => 'permission:change password' ]);
        // Route::get('xls-per-unit', [ 'uses' => 'AbsenCtrl@xlsUnit_v2', 'middleware' => 'permission:change password' ]);
        Route::get('xls', [ 'uses' => 'AbsenCtrl@xlsUnit_v2', 'middleware' => 'permission:change password' ]);
        // Route::get('unit/xls', [ 'uses' => 'AbsenCtrl@xlsUnit', 'middleware' => 'permission:index report peg' ]);
        Route::get('employee/{id}/from/{from}/to/{to}', [ 'uses' => 'AbsenCtrl@getEmployee', 'middleware' => 'permission:index report peg' ]);
        Route::post('employee/store', [ 'uses' => 'AbsenCtrl@storeEmployee', 'middleware' => 'permission:create report peg|update report peg' ]);
    });
    Route::prefix('holidays')->group(function() {
        Route::get('dt', [ 'uses' => 'HolidayCtrl@dt', 'middleware' => 'permission:index holiday' ]);
        Route::post('store', [ 'uses' => 'HolidayCtrl@store', 'middleware' => 'permission:create holiday|update holiday' ]);
        Route::get('delete/{id}', [ 'uses' => 'HolidayCtrl@destroy', 'middleware' => 'permission:delete holiday' ]);
        Route::get('{id}', [ 'uses' => 'HolidayCtrl@get', 'middleware' => 'permission:index holiday' ]);
    });
    Route::get('dump-rpt/{unit}/from/{from}/to/{to}', [ 'uses' => 'AbsenCtrl@apiGenReport' ]);
    Route::prefix('siaga')->group(function() {
        Route::prefix('shift')->group(function() {
            // Route::get('dt', [ 'uses' => 'SiagaShiftCtrl@dt', 'middleware' => 'permission:index shift' ]);
            Route::post('store', [ 'uses' => 'SiagaShiftCtrl@store', 'middleware' => 'permission:create shift|update shift' ]);
            Route::get('delete/{id}', [ 'uses' => 'SiagaShiftCtrl@destroy', 'middleware' => 'permission:delete shift' ]);
            Route::get('{id}', [ 'uses' => 'SiagaShiftCtrl@get', 'middleware' => 'permission:index shift' ]);
        });
        Route::prefix('assign')->group(function() {
            Route::get('dt', [ 'uses' => 'SiagaAssignCtrl@dt', 'middleware' => 'permission:index assign' ]);
            Route::post('store', [ 'uses' => 'SiagaAssignCtrl@store', 'middleware' => 'permission:create assign|update assign' ]);
            Route::get('delete/{id}', [ 'uses' => 'SiagaAssignCtrl@destroy', 'middleware' => 'permission:delete assign' ]);
            Route::get('get/{month}', [ 'uses' => 'SiagaAssignCtrl@getByMonth', 'middleware' => 'permission:index assign' ]);
            Route::get('{id}', [ 'uses' => 'SiagaAssignCtrl@get', 'middleware' => 'permission:index assign' ]);
        });
        Route::get('rekap/xls', [ 'uses' => 'SiagaAbsentCtrl@xlsRekap' ]);
        Route::prefix('position')->group(function() {
            Route::get('dt', [ 'uses' => 'SiagaPositionCtrl@dt', 'middleware' => 'permission:index position' ]);
            Route::post('store', [ 'uses' => 'SiagaPositionCtrl@store', 'middleware' => 'permission:create position|update position' ]);
            Route::get('delete/{id}', [ 'uses' => 'SiagaPositionCtrl@destroy', 'middleware' => 'permission:delete position' ]);
            Route::get('{id}', [ 'uses' => 'SiagaPositionCtrl@get', 'middleware' => 'permission:index position' ]);
        });
    });
    Route::prefix('employees')->group(function() {
        Route::get('dt', [ 'uses' => 'EmployeeCtrl@dt' ]);
        Route::post('store', [ 'uses' => 'EmployeeCtrl@store', 'middleware' => 'permission:create employee|update employee' ]);
        Route::get('{id}', [ 'uses' => 'EmployeeCtrl@get', 'middleware' => 'permission:index employee' ]);
        Route::get('delete/{id}', [ 'uses' => 'EmployeeCtrl@destroy', 'middleware' => 'permission:delete employee' ]);
    });
    Route::prefix('positions')->group(function() {
        Route::get('dt', [ 'uses' => 'PositionCtrl@dt' ]);
    });
    Route::prefix('levels')->group(function() {
        Route::get('dt', [ 'uses' => 'LevelCtrl@dt' ]);
    });
});
