<?php

namespace App\Schema;

class Label
{
    public static function get($key)
    {
        $path = 'label.example';
        if(file_exists(base_path('config/label.php')))
        {
            $path = 'label';
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
