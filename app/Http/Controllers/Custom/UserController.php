<?php

namespace App\Http\Controllers\Custom;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class UserController extends Controller
{
    public function updatePassword(Request $request)
    {
    	$primary  = $request->primary;
    	$password = $request->password;

    	$this->validate($request, [
	        'primary' 	=> 'required|numeric',
	        'password' 	=> 'required',
	    ]);

    	DB::table('users')
    	->where('id', $primary)
    	->update([
    		'password'	=>	bcrypt($password)
    	]);

    	return response()->json(true);
    }
}
