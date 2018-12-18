<?php

namespace App\Schema;

class Icon
{
    public static function get($key)
    {
        $path = 'icon.example';
        if(file_exists(base_path('config/icon.php')))
        {
            $path = 'icon';
        }

        if(array_key_exists($key, config($path))) 
        {
            return config($path)[$key];
        }
        else 
        {
            return '';
        }
    }
}
