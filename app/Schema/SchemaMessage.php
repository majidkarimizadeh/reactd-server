<?php

namespace App\Schema;
use DB;

class SchemaMessage
{
    static $items = [
        [
            'meta_key'  =>  'required',
            'meta_value' => 'The " %s " field is required', 
        ],
        [
            'meta_key'  =>  'numeric',
            'meta_value' => 'The " %s " must be a number', 
        ],
        [
            'meta_key'  =>  'minVal',
            'meta_value' => 'The " %s " must be at least %d', 
        ],
        [
            'meta_key'  =>  'maxVal',
            'meta_value' => 'The " %s " may not be greater than %d', 
        ],
        [
            'meta_key'  =>  'minLen',
            'meta_value' => 'The " %s " must be at least %d characters', 
        ],
        [
            'meta_key'  =>  'maxLen',
            'meta_value' => 'The " %s " may not be greater than %d characters', 
        ],
        [
            'meta_key'  =>  'minRel',
            'meta_value' => 'The " %s " must be at least " %s " field', 
        ],
        [
            'meta_key'  =>  'maxRel',
            'meta_value' => 'The " %s " may not be greater than " %s " field', 
        ],
        [
            'meta_key'  =>  'minRelNow',
            'meta_value' => 'The " %s " must be a date after now', 
        ],
        [
            'meta_key'  =>  'maxRelNow',
            'meta_value' => 'The " %s " must be a date before now', 
        ],
    ];

    public static function createSchemaMessage()
    {
        DB::table('schema_msg')->insert(self::$items);
    }
}
