<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

#Route::get('/home', 'HomeController@index')->name('home');
Route::get('/home', 'ImageController@index');

Route::post('webLoginPost', 'UserController@webLoginPost')->name('webLoginPost');

Route::group(['middleware' => ['auth']], function() {
    // your routes
	Route::get('list-wallpaper', 'ImageController@index')->name('list');
	Route::post('store-wallpaper', 'ImageController@store')->name('store');
	
	Route::post('edit-wallpaper', 'ImageController@edit')->name('edit');
	Route::post('delete-wallpaper', 'ImageController@destroy')->name('destroy');
	
	Route::post('reorder-wallpaper-sequence', 'ImageController@reorderSequence')->name('reorderSequence');
	
	Route::get('/wallpaperlisting/{image}', 'ImageController@show');
	
	
	// your routes for user subscription listing section
	Route::get('list-user-subscription', 'UserController@sublist')->name('listUserSubscription');
	Route::post('delete-user-subscription', 'UserController@destroy')->name('destroy');
	
	Route::post('view-subscription', 'UserController@viewSubscription')->name('viewSubscription');
	
	Route::get('/privacy-policy', 'ImageController@privacypolicy');
});