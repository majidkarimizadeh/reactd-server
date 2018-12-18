<?php

namespace App\Schema;
use Schema;

class Translation
{
    public static function has($key)
    {
        if(self::existTranslationTable($key))
        {
            return true;
        }

        $path = 'translation.example';
        if(file_exists(base_path('config/translation.php')))
        {
            $path = 'translation';
        }

        return array_key_exists($key, config($path));
    }

    public static function get($key)
    {
        if(self::has($key)) 
        {
            if(self::existTranslationTable($key))
            {
                return [
                    str_singular($key) . '_id'  =>  $key . '_translation'
                ];
            }

            $path = 'translation.example';
            if(file_exists(base_path('config/translation.php')))
            {
                $path = 'translation';
            }
            return config($path)[$key];
        }
        else 
        {
            return '';
        }
    }

    public static function hasColumn($table, $column)
    {
        if(
            self::existTranslationTable($table) &&
            Schema::hasColumn($table . '_translation', $column) &&
            Schema::hasColumn($table, $column) &&
            ($column !== 'id')
        )
        {
            return true;
        }
        return false;
    }

    public static function existTranslationTable($key)
    {
        return Schema::hasTable($key . '_translation') &&
        Schema::hasColumn($key . '_translation', str_singular($key) . '_id') &&
        Schema::hasColumn($key . '_translation', 'locale');
    }
}
