<?php

namespace App\Http\Controllers;
use App\Schema\Security\Security;
use App\Schema\Filter\Filter;
use Illuminate\Http\Request;
use DB;

class TableController extends Controller
{
    public function getTable(Request $request)
    {
        $url    =   $request->url;
        $table  =   DB::table('schema')
                        ->where('meta_value->tbl', null)
                        ->where('meta_value->url', $url)
                        ->select(
                            DB::raw("meta_value->>'$.dtl' as details"),
                            'meta_key', 
                            'meta_value'
                        )
                        ->first();

        if(!$table) 
        {
            return response('Not found', 404);
        }
        $tableName = $table->meta_key;

        $securityResult = Security::tableSecurity($tableName, 'select');
        if(!$securityResult['hasPermission']) 
        {
            return response('Permission deny', 403);
        }

        $detailsName    = json_decode($table->details);
        $details        = Security::detailSecurity(array_merge($detailsName, (array)$morphsName));
        $columns        = Filter::getTableColumns($tableName);
        $totalRows      = DB::table($tableName)->count();

        return response()->json([
            'details'       =>  $details,
            'table'         =>  $table->meta_value,
            'cols'          =>  json_encode($columns),
            'perm'          =>  $securityResult['permission'],
            'totalRows'     =>  $totalRows
        ]);

    }
}
