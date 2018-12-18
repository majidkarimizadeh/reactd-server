<?php

namespace App\Schema;

class PlaceHolder
{
    public static function get($key)
    {
        $path = 'placeholder.example';
        if(file_exists(base_path('config/placeholder.php')))
        {
            $path = 'placeholder';
        }

        if(array_key_exists($key, config($path))) 
        {
            return config($path)[$key];
        }
        else 
        {
            return $key;
        }
    }
}
