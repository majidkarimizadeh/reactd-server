<?php

namespace App\Http\Controllers;
use App\Schema\Icon;
use App\Schema\Label;
use App\Schema\Translation;
use App\Schema\PlaceHolder;
use App\Schema\Menu;
use App\Schema\SchemaMessage;
use App\Schema\TypeChecker;
use DB;

class SchemaController extends Controller
{
    public function generateSchema()
    {
        DB::table('schema')->truncate();
        DB::table('schema_msg')->truncate();
        DB::table('schema_actions')->truncate();
        DB::table('look_ups')->truncate();

        Menu::createMenu();
        SchemaMessage::createSchemaMessage();

        $tables = array_diff(
            // array_map('reset', DB::select('SHOW TABLES')), 
            array_map('reset', DB::select("SHOW TABLES WHERE `Tables_in_". DB::connection()->getDatabaseName() ."` NOT LIKE '%translation%' ")), 
            ['migrations', 'schema', 'schema_msg', 'schema_actions', 'look_ups']
        );

        $schemaRecords = [];
        foreach ($tables as $table) 
        {
            $primaryKey = DB::select("SHOW KEYS FROM {$table} WHERE Key_name = 'PRIMARY' ");
            $columns    = $this->getColumns($table);
            $details    = $this->getDetails($table);
            $childrens  = $this->getChildren($table);

            // $editableColumns = array_values(
            //     array_diff($columns, ['id', 'created_at', 'updated_at'])
            // );
            $editableColumnsKey = [];
            foreach (array_values($columns) as $key => $editableColumn) 
            {
                if(
                    $editableColumn != 'id' &&
                    $editableColumn != 'created_at' &&
                    $editableColumn != 'updated_at'
                ) 
                {
                    $editableColumnsKey[] = ($key + 1) * 10;
                }
            }
            $viewableColumnsKey  = array_values( range(10, count($columns) * 10, 10) );

            $schemaRecordMetaKey    = $table;
            $schemaRecordMetaValue  = [
                'nme'       =>  $table,
                'pk'        =>  count($primaryKey) ? $primaryKey[0]->Column_name : '',
                'url'       =>  $table,
                'typ'       =>  'tbl',
                'lbl'       =>  Label::get($table),
                'icn'       =>  Icon::get($table),
                'edt'       =>  $editableColumnsKey,
                'crt'       =>  $editableColumnsKey,
                'shw'       =>  $viewableColumnsKey,
                'lst'       =>  $viewableColumnsKey,
                'dtl'       =>  $details,
                'chl'       =>  $childrens
            ];

            if(Translation::has($table)) 
            {
                $schemaRecordMetaValue['trs'] = Translation::get($table);
            }

            $schemaRecords[] = [
                'meta_key'      =>  $schemaRecordMetaKey,
                'meta_value'    =>  json_encode($schemaRecordMetaValue)
            ];

            foreach ($columns as $key => $column) 
            {
                $type   = $this->getColumnType($table, $column);
                $lookUp = $this->getColumnLookup($table, $column);

                $schemaRecordMetaValue = [
                    'no'    =>  ( $key + 1 ) * 10,
                    'nme'   =>  $column,
                    'typ'   =>  $type,
                    'tbl'   =>  $table,
                    'lbl'   =>  Label::get($column),
                    'plh'   =>  PlaceHolder::get($column),
                ];

                if($lookUp) 
                {
                    $schemaRecordMetaValue['cnt'] = 'lku';
                    $schemaRecordMetaValue['rdf'] = $this->generateLookUp($lookUp);
                }
                else if(TypeChecker::isPassword($column))
                {
                    $schemaRecordMetaValue['cnt']   = 'pas';
                }
                else if(TypeChecker::isImage($column))
                {
                    $schemaRecordMetaValue['cnt']   = 'img';
                }
                else if (TypeChecker::isGeoPoint($column)) 
                {
                    $schemaRecordMetaValue['cnt'] = 'geo';
                }
                else if(TypeChecker::isWysiwyg($type)) 
                {
                    $schemaRecordMetaValue['cnt'] = 'wys';
                }
                else if (TypeChecker::isString($type)) 
                {
                    $schemaRecordMetaValue['cnt'] = 'str';
                }
                else if(TypeChecker::isBool($type)) 
                {
                    $schemaRecordMetaValue['cnt'] = 'bol';
                }
                else if (TypeChecker::isNumber($type)) 
                {
                    $schemaRecordMetaValue['cnt'] = 'num';
                    $schemaRecordMetaValue['vld'] = [
                        'numeric'   =>  true
                    ];
                }
                else if(TypeChecker::isDate($type)) 
                {
                    $schemaRecordMetaValue['cnt'] = 'dat';
                    $schemaRecordMetaValue['jal'] = false;
                    $schemaRecordMetaValue['tim'] = false;
                }

                $schemaRecordMetaKey = $table . '_' . $column;
                $schemaRecords[] = [
                    'meta_key'      =>  $schemaRecordMetaKey,
                    'meta_value'    =>  json_encode($schemaRecordMetaValue),
                ];
            }
        }
        DB::table('schema')->insert($schemaRecords);

        $this->generateSchemaActions();
        return 'Schema generated successfully';

    }

    private function getColumns($table)
    {
        return  DB::table('INFORMATION_SCHEMA.COLUMNS')
                ->where('TABLE_SCHEMA', DB::connection()->getDatabaseName())
                ->where('TABLE_NAME', $table)
                ->select('COLUMN_NAME')
                ->get()
                ->pluck('COLUMN_NAME')
                ->toArray();
    }

    private function getDetails($table)
    {
        return  DB::table('INFORMATION_SCHEMA.KEY_COLUMN_USAGE')
                ->where('REFERENCED_TABLE_SCHEMA', DB::connection()->getDatabaseName())
                ->where('REFERENCED_TABLE_NAME', $table)
                ->select('TABLE_NAME')
                ->get()
                ->pluck('TABLE_NAME')
                ->toArray();
    }

    private function getChildren($table)
    {
        return  DB::table('INFORMATION_SCHEMA.KEY_COLUMN_USAGE')
                ->where('REFERENCED_TABLE_SCHEMA', DB::connection()->getDatabaseName())
                ->where('TABLE_NAME', $table)
                ->select('COLUMN_NAME', 'REFERENCED_TABLE_NAME')
                ->get()
                ->pluck('COLUMN_NAME', 'REFERENCED_TABLE_NAME')
                ->toArray();
    }

    private function getColumnType($table, $column)
    {
        return  DB::table('INFORMATION_SCHEMA.COLUMNS')
                ->where('TABLE_SCHEMA', DB::connection()->getDatabaseName())
                ->where('TABLE_NAME', $table)
                ->where('COLUMN_NAME', $column)
                ->select('DATA_TYPE')
                ->get()
                ->pluck('DATA_TYPE')
                ->first();
    }

    private function getColumnLookup($table, $column)
    {
        return  DB::table('INFORMATION_SCHEMA.KEY_COLUMN_USAGE')
                ->where('REFERENCED_TABLE_SCHEMA', DB::connection()->getDatabaseName())
                ->where('TABLE_NAME', $table)
                ->where('COLUMN_NAME', $column)
                ->select('REFERENCED_COLUMN_NAME', 'REFERENCED_TABLE_NAME')
                ->get()
                ->first();
    }

    private function generateLookUp($lookUp)
    {
        $storeKey       = $lookUp->REFERENCED_COLUMN_NAME;
        $referenceTable = $lookUp->REFERENCED_TABLE_NAME;
        $displayKey     = 'name';

        $lookupRecord = [
            'display_key'   =>  $displayKey,
            'query'         =>  "SELECT :store_key as value, :display_key as label FROM :table :condition "
        ];

        if(Translation::has($referenceTable)) 
        {
            $lookupRecord['store_key'] = Translation::has($referenceTable)['key'];
            $lookupRecord['table'] = Translation::has($referenceTable)['table'];
        }
        else 
        {
            $lookupRecord['store_key'] = $storeKey;
            $lookupRecord['table'] = $referenceTable;
        }
        return DB::table('look_ups')->insertGetId($lookupRecord);
    }

    private function generateSchemaActions()
    {
        $schemaRecords  =   DB::table('schema')
                            ->where('meta_key', '<>', 'main_menubar')
                            ->where('meta_value->tbl', null)
                            ->select('id')
                            ->get()
                            ->pluck('id')
                            ->toArray();

        $roles = DB::table('roles')->get();

        $schemaActionsRecords = [];
        foreach ($schemaRecords as $schemaRecord) 
        {
            foreach($roles as $role) 
            {
                $schemaActionsRecords[] = [
                    'schema_id'     =>  $schemaRecord,
                    'role_id'       =>  $role->id,
                    'perm'          =>  '15'
                ];
            }
        }
        DB::table('schema_actions')->insert($schemaActionsRecords);
    }
}
