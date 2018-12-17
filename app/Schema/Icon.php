<?php

namespace App\Schema;

class Icon
{
    static $icons = [
    	'users'		=>	'fa fa-users'
    ];

    public static function get($key)
    {
        if(array_key_exists($key, self::$icons)) 
        {
            return self::$icons[$key];
        }
        else 
        {
            return '';
        }
    }
}
