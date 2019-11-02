<?php

use Illuminate\Http\Request;

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

Route::prefix('auth')->group(function () {

    Route::group(['middleware' => ['throttle:20,5']], function () {
        Route::post('/register', 'Auth\RegisterController@register');
        Route::post('/login', 'Auth\LoginController@login');
    });

});

Route::prefix('account')->group(function () {

    Route::group(['middleware' => ['jwt.auth']], function () {
        Route::get('/profile', 'User\UserController@index');
        Route::post('/logout', 'User\UserController@logout');
    });

});
