<?php

namespace App\Http\Controllers;
use App\Schema\Security\Security;
use DB;

class MenuController extends Controller
{
    public function getMenu()
    {
        $menuBarRecord = DB::table('schema')
                        ->where('meta_key', 'main_menubar')
                        ->pluck('meta_value')
                        ->first();

        $menu       = json_decode($menuBarRecord, true);
        $secureMenu = Security::menuSecurity($menu);
        return response()->json([
            'menu'    =>  $secureMenu,
        ]);
    }
}
