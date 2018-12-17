<?php

namespace App\Schema;

class Label
{
    static $label = [
        'users'     =>      'Users',
        'roles'     =>      'Roles',
    ];

    public static function get($key)
    {
        if(array_key_exists($key, self::$label)) 
        {
            return self::$label[$key];
        }
        else 
        {
            return $key;
        }
    }
}
