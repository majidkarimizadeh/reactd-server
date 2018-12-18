<?php

namespace App\Schema;
use DB;

class SchemaMessage
{
    public static function createSchemaMessage()
    {
    	$path = 'schemaMessage.example';
        if(file_exists(base_path('config/schemaMessage.php')))
        {
            $path = 'schemaMessage';
        }

        DB::table('schema_msg')->insert(config($path));
    }
}
