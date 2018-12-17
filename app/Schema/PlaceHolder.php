<?php

namespace App\Schema;

class PlaceHolder
{
    static $placeholder = [
        'user_id'     =>      'ID'
    ];

    public static function get($key)
    {
        if(array_key_exists($key, self::$placeholder)) 
        {
            return self::$placeholder[$key];
        }
        else 
        {
            return $key;
        }
    }
}
