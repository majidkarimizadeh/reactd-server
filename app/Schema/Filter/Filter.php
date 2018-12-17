<?php

namespace App\Schema\Filter;
use DB;

class Filter {

	public static function getTableColumns($tableName, $which = 'lst')
	{
	    $schemaRecord = DB::table('schema')
	    ->where('meta_key', $tableName)
	    ->where('meta_value->tbl', null)
	    ->select(
	    	DB::raw(" meta_value->>' $.". $which ." ' as col "),
	    	DB::raw(" meta_value->>'$.pk' as pk ")
	    )
	    ->first();

	    if(!$schemaRecord) return [];

	    $pk = $schemaRecord->pk;
	    $col = json_decode($schemaRecord->col);

	    $query = DB::table('schema')
	    	->where('meta_key', 'LIKE', "{$tableName}_%")
	    	->where('meta_key', 'NOT LIKE', '%translation%');

	    if($col && count($col)) 
	    {
	        $query->whereIn('meta_value->no', $col);
	    }

	    $columns = $query->select('meta_value')
	    	->get()
	    	->pluck('meta_value');

	    $columnsDecoded = [];
		foreach ($columns as $column) 
		{
    		$columnsDecoded[] = json_decode($column);
		}
	    return $columnsDecoded;
	}
}