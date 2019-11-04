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

    Route::group(['middleware' => ['throttle:60,10']], function () {

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

Route::prefix('articles')->group(function () {

    Route::group(['middleware' => ['jwt.auth']], function () {

        Route::get('/', 'Article\ArticleController@index');
        Route::post('/', 'Article\ArticleController@store');        
        Route::get('/{article}', 'Article\ArticleController@show');
        Route::patch('/{article}', 'Article\ArticleController@update');
        Route::delete('/{article}', 'Article\ArticleController@destroy');

    });

});