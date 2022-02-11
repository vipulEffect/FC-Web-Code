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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

///////////////////////////////////////////////////////////////////////////
///////////// For Android Users ////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////

Route::post('getWallpapersAtHomeForAndroidUsers', 'Api\AuthController@getWallpapersAtHomeForAndroidUsers'); //For get Wallpapers At Home wallpaper display

Route::post('selectedWallpaperForAndroidUsers', 'Api\AuthController@selectedWallpaperForAndroidUsers'); //For selected Wallpaper from Home wallpaper

Route::post('addUpdSubscriptionForAndroidUsers', 'Api\AuthController@addUpdSubscriptionForAndroidUsers');  //For add edit subscription

Route::post('getSubscriptionInfoForAndroidUsers', 'Api\AuthController@getSubscriptionInfoForAndroidUsers'); //For get user subscription info


///////////////////////////////////////////////////////////////////////////
///////////// For IOS Users ////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////

Route::post('getWallpapersAtHome', 'Api\AuthController@getWallpapersAtHome'); //For get Wallpapers At Home wallpaper display

Route::post('selectedWallpaper', 'Api\AuthController@selectedWallpaper'); //For selected Wallpaper from Home wallpaper

Route::post('addUpdSubscription', 'Api\AuthController@addUpdSubscription'); //For add edit subscription for user

Route::post('getSubscriptionInfo', 'Api\AuthController@getSubscriptionInfo'); //For get user subscription info