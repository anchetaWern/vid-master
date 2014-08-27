<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::filter('nocache', function($route, $request, $response)
{
  $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
  $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0,    pre-check=0');
  $response->header('Pragma', 'no-cache');
  return $response;
});

Route::pattern('long_id', '[a-z0-9\-\+]{8,160}');
Route::pattern('id', '[0-9]+');

Route::get('/', 'HomeController@index');
Route::get('/signup', 'HomeController@signup');
Route::post('/signup', 'HomeController@doSignup');

Route::get('/login', 'HomeController@login');
Route::post('/login', 'HomeController@doLogin');

Route::group(array('before' => 'auth', 'after' => 'nocache'), function()
{
	Route::get('/admin', 'AdminController@index');

	Route::get('/websites/new', 'AdminController@newWebsite');
	Route::post('/websites', 'AdminController@createWebsite');

	Route::get('/websites', 'AdminController@websites');
});