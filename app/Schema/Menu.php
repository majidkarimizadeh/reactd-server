<?php

namespace App\Schema;
use DB;

class Menu
{
    public static function createMenu()
    {
    	$path = 'menu.example';
    	if(file_exists(base_path('config/menu.php')))
    	{
    		$path = 'menu';
    	}
    	
        DB::table('schema')->insert([
            'meta_key'      =>  'main_menubar',
            'meta_value'    =>  json_encode(config($path))
        ]);
    }
}
