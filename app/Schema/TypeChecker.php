<?php

namespace App\Schema;

class TypeChecker
{
    public static function isPassword($column)
    {
        return  (strpos(strtolower($column), 'password') !== false);
    }

    public static function isImage($column)
    {
        return  (strpos(strtolower($column), 'img') !== false) || 
                (strpos(strtolower($column), 'image') !== false);
    }

    public static function isGeoPoint($column)
    {
        return  (strpos(strtolower($column), 'geopoint') !== false);
    }

    public static function isWysiwyg($type)
    {
        return  ($type == 'longtext') ||
                ($type == 'text');
    }

    public static function isBool($type)
    {
        return  ($type == 'tinyint') || 
                ($type == 'boolean');
    }

    public static function isNumber($type)
    {
        return  $type == 'int';
    }

    public static function isDate($type)
    {
        return  ($type == 'timestamp') || 
                ($type == 'date') || 
                ($type == 'datetime');
    }

    public static function isString($type)
    {
        return  $type == 'varchar';
    }

}
