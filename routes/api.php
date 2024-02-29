<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['middleware' => ['cors', 'json.response']], function () { 
    /** Auth group routes */
    Route::prefix('auth')->group(function () {
        Route::post('login', 'Auth\AuthAPIController@login')->name('auth.login');
        Route::post('refresh-token','Auth\AuthAPIController@refreshToken')->name('auth.refresh-token');
        Route::post('register','Auth\AuthAPIController@register')->name('auth.register'); 
        Route::post('logout', 'Auth\AuthAPIController@logout')->middleware('auth:api')->name('auth.logout');
        Route::post('details', 'Auth\AuthAPIController@details')->middleware('auth:api')->name('auth.detils'); // middleware(, 'verified')
        Route::post('email/forgot-password', 'Auth\AuthAPIController@forgotPassword');
        Route::put('change-password', 'Auth\AuthAPIController@changePassword')->middleware('auth:api');
    });


    Route::get('/unit-pembangkit/get-data', 'UnitPembangkitAPIController@getall');  
    Route::group(['middleware' => ['auth:api']], function () {  

        Route::post('/equipment/import', 'EquipmentAPIController@import');   
        Route::resource('equipment', EquipmentAPIController::class);
        Route::resource('non-rutin-vibrasi', NonRutinVibrasiAPIController::class);
        Route::get('/rekomendasi/persentase', 'RekomendasiAPIController@persentase'); 
        Route::resource('rekomendasi', RekomendasiAPIController::class);

        //TERMOGRAFI
        Route::get('/termografi/trend-data', 'TermografiAPIController@data_trend');  
        Route::resource('termografi', TermografiAPIController::class);  
        //TRIBOLOGI
        Route::get('/tribologi/trend-data', 'TribologiAPIController@data_trend');  
        Route::resource('tribologi', TribologiAPIController::class);
        //VIBRASI
        Route::get('/vibrasi/trend-data', 'VibrasiAPIController@data_trend');  
        Route::resource('vibrasi', VibrasiAPIController::class);  

        Route::post('/unit-item/import', 'UnitItemAPIController@import');  
        Route::resource('unit-item', UnitItemAPIController::class); 
        Route::resource('unit-pembangkit', UnitPembangkitAPIController::class);    
        Route::post('/users/change-status', 'UserManagementAPIController@changeStatus'); 
        Route::resource('user-role',  UserRoleAPIController::class);
        Route::put('users/reset-password', 'UserManagementAPIController@resetPassword')->middleware('auth:api'); 
        Route::resource('users', UserManagementAPIController::class); 
        Route::post('/format-export', 'FormatExportAPIController@show'); 
        Route::get('/statistik/vibrasi', 'StatistikAPIController@vibrasi'); 
        Route::get('/statistik/termografi', 'StatistikAPIController@termografi'); 
        Route::prefix('laporan')->group(function () {
            Route::get('vibrasi', 'LaporanAPIController@vibrasi')->name('laporan.vibrasi'); 
            Route::get('presentasi', 'LaporanAPIController@presentasi')->name('laporan.presentasi');  
            Route::get('summary', 'LaporanAPIController@summary')->name('laporan.summary-pdf'); 
        });  
    });  
});       


