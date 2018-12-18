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

$prefix = '/v1';

Route::group([
	'prefix'	=>	$prefix
], 
function()
{
	Route::post('login', [
		'as'	=>	'login',
		'uses'	=>	'Auth\AuthController@login'
	]);
});


Route::group([
	// 'middleware' => 'auth:api',
	'prefix'	=>	$prefix
], 
function()
{

	Route::get('generate-schema', 'SchemaController@generateSchema');

	Route::post('get-menu', 'MenuController@getMenu');

	Route::post('get-table', 'TableController@getTable');

	Route::post('get-data', 'DataController@getData');
	
	Route::post('get-row', 'DataController@getRow');

	Route::post('update', 'UpdateController@update');

	Route::post('store', 'StoreController@store');

	Route::post('destroy', 'DestroyController@destroy');

	Route::post('look-up', 'LookUpController@lookUp');

	Route::post('wysiwyg-update', 'WysiwygController@wysiwygUpdate');

	Route::post('wysiwyg-destroy', 'WysiwygController@wysiwygDestroy');

    Route::post('logout', 'Auth\AuthController@logout');

    // custom
    Route::post('get-roles', 'Custom\RoleController@index');
	Route::post('update-roles', 'Custom\RoleController@update');
	// end custom

});
