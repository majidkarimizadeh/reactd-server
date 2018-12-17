<?php

namespace App\Schema;

class Translation
{
    static $translation = [
    	'XXX' => [
            'XXX_id' =>  'XXX_translation',
        ],
    ];

    public static function has($key)
    {
        return array_key_exists($key, self::$translation);
    }

    public static function get($key)
    {
        if(self::has($key)) 
        {
            return self::$translation[$key];
        }
        else 
        {
            return '';
        }
    }
}
