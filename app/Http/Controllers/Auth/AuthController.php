<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $this->validate($request, [
            'email'     => 'required|email',
            'password'  => 'required',
        ]);

        $inputs = $request->only('email', 'password');
        $token  = auth()->attempt($inputs);
        $user   = auth()->user();
        if($user && $token)
        {
            return response()->json([
                'token' =>  $token,
                'user'  =>  collect($user->toArray())
                            ->only(['full_name', 'img'])
                            ->all()
            ]);
        }
        else
        {
            return response()->json([
                'status'    =>  404,
                'message'   =>  'User Not found'
            ], 404);
        }
    }

    public function logout()
    {
        return auth()->logout();
    }
}
