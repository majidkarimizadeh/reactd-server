<?php

// your custom routes here:

Route::post('get-roles', 'RoleController@index');
Route::post('update-roles', 'RoleController@update');

Route::post('update-password', 'UserController@updatePassword');