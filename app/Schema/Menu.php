<?php

namespace App\Schema;
use DB;

class Menu
{
    static $items = [
            [
                "lbl"     =>  "User Management",
                "icn"      =>  "fa fa-cog",
                "itm"     =>  [
                    [ "lbl" =>  'Users', "url"   =>  "users", "icn"  =>  'fa fa-users' ],
                    [ "lbl" =>  'Roles', "url"   =>  "roles", "icn"  =>  'fa fa-lock' ],
                ]
            ],
        ];

    public static function createMenu()
    {
        DB::table('schema')->insert([
            'meta_key'      =>  'main_menubar',
            'meta_value'    =>  json_encode(self::$items)
        ]);
    }
}
