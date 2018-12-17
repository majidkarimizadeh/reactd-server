<?php

namespace App\Http\Controllers;
use App\Schema\Filter\Filter;
use App\Schema\Security\Security;
use App\Schema\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use DB;

class DataController extends Controller
{

    public function getData(Request $request)
    {
        $url        = $request->url;
        $lang       = $request->lang ? $request->lang : false;
        $start      = $request->has('start') ? $request->start : 0;
        $limit      = ($request->has('limit') && $request->limit <= 1000) ? $request->limit : 9;
        $conditions = $request->has('conditions') ? json_decode($request->conditions, true) : [];
        
        $where = $filters = $cols = $joins = "";
        $translationColumns = [];

        $schemaRecord = DB::table('schema')
        ->where('meta_value->url', $url)
        ->select(
            'meta_key',
            DB::raw(" meta_value->>'$.lst' as list "),
            DB::raw(" meta_value->>'$.pk' as pk "),
            DB::raw(" meta_value->>'$.trs' as translation ")
        )
        ->first();

        if(!$schemaRecord) return resposne('Not found.', 404);

        $tableName      = $schemaRecord->meta_key;
        $securityResult = Security::tableSecurity($tableName, 'select');

        if(!$securityResult['hasPermission']) 
        {
            return response('Permission deny', 403);
        }

        $pk             = $schemaRecord->pk;
        $list           = json_decode($schemaRecord->list);
        $translation    = json_decode($schemaRecord->translation);
        $columns        = Filter::getTableColumns($tableName);
        if(count($columns) === 0)
        {
            $filters = " T0.* ";
        }
        else 
        {
            foreach ($columns as $key => $column)
            {
                if(property_exists($column, 'trs')) 
                {
                    $translationColumns[] = $column;
                }
                else 
                {
                    $filters .= " T0.{$column->nme} ,";
                }
            }
            $filters = rtrim($filters, ',');
        }
        $query = DB::table('schema')
            ->join('look_ups', 'look_ups.id', '=', DB::raw(" meta_value->>'$.rdf' "))
            ->where('meta_key', 'LIKE', "{$tableName}_%")
            ->where('meta_key', 'NOT LIKE', '%translation%')
            ->where('meta_value->controller', 'lookup');

        if($list && count($list))
        {
            $query = $query->whereIn('meta_value->no', $list);
        }
        
        $lookups    =  $query->select(
                            DB::raw(" meta_value->>'$.name' as name "),
                            'look_ups.*'
                        )
                        ->get();

        $index = 0;
        foreach ($lookups as $key => $lookup) 
        {
            $index = $key + 1;
            $cols .= ", T{$index}.{$lookup->display_key} as join_{$lookup->name} ";
            if(strpos($lookup->table, 'translation') !== false && $lang)  
            {
                $joins .= " LEFT JOIN {$lookup->table} as T{$index} ON T0.{$lookup->name} = T{$index}.{$lookup->store_key} AND T{$index}.locale = '{$lang}' ";
            } 
            else
            {
                $joins .= " LEFT JOIN {$lookup->table} as T{$index} ON T0.{$lookup->name} = T{$index}.{$lookup->store_key} ";
            }
        }


        if($translation) 
        {
            $translationTable = current($translation);
            $translationTableKey = key($translation);
            $index = $index + 1;
            if(count($translationColumns)) 
            {
                foreach ($translationColumns as $translationColumn) 
                {
                    $cols .= ", T{$index}.{$translationColumn->nme} ";
                }
            }
            $joins .= " LEFT JOIN {$translationTable} as T{$index} ON T0.{$pk} = T{$index}.{$translationTableKey} ";
        }

        $where = QueryBuilder::whereBuilder($conditions);
        if($where) 
        {
            $where = " WHERE " . $where;
        }

        if($translation && $lang) 
        {
            if($where) 
            {
                $where .= " AND T{$index}.locale = '{$lang}' ";
            }
            else 
            {
                $where .= " WHERE T{$index}.locale = '{$lang}' ";
            }
        }

        $query = "SELECT {$filters} {$cols} FROM {$tableName} as T0 {$joins} {$where} ORDER BY T0.{$pk} DESC LIMIT $start, $limit ";
        $countQuery = "SELECT COUNT(*) as cnt FROM {$tableName} as T0 {$joins} {$where}";
        
        return response()->json([
            'totalRows'   =>  DB::select($countQuery)[0]->cnt,
            'data'      =>  DB::select($query)
        ]);
    }

    public function getRow(Request $request)
    {
        $url        = $request->url;
        $mode       = $request->mode;
        $primary    = $request->primary;
        $lang       = $request->lang ? $request->lang : false;

        $where = $filters = $cols = $joins = "";
        $translationColumns = [];

        if(!in_array($mode, ['edt', 'shw'])) 
        {
            return response('mode value is not supported', 422);
        }

        $schemaRecord = DB::table('schema')
        ->where('meta_value->url', $url)
        ->select(
            'meta_key',
            DB::raw(" meta_value->>'$." . $mode ."' as neededCols "),
            DB::raw(" meta_value->>'$.pk' as pk "),
            DB::raw(" meta_value->>'$.trs' as translation ")
        )
        ->first();

        if(!$schemaRecord) return resposne('Not found.', 404);

        $tableName      = $schemaRecord->meta_key;
        $securityResult = Security::tableSecurity($tableName, 'select');

        if(!$securityResult['hasPermission']) 
        {
            return response('Permission deny', 403);
        }

        $pk             = $schemaRecord->pk;
        $neededCols     = json_decode($schemaRecord->neededCols);
        $translation    = json_decode($schemaRecord->translation);
        $columns        = Filter::getTableColumns($tableName, $mode);
        if(count($columns) === 0)
        {
            $filters = " T0.* ";
        }
        else 
        {
            foreach ($columns as $key => $column)
            {
                if(property_exists($column, 'trs')) 
                {
                    $translationColumns[] = $column;
                }
                else 
                {
                    $filters .= " T0.{$column->nme} ,";
                }
            }
            $filters = rtrim($filters, ',');
        }
        $query = DB::table('schema')
            ->join('look_ups', 'look_ups.id', '=', DB::raw(" meta_value->>'$.rdf' "))
            ->where('meta_key', 'LIKE', "{$tableName}_%")
            ->where('meta_key', 'NOT LIKE', '%translation%')
            ->where('meta_value->controller', 'lookup');

        if($neededCols && count($neededCols))
        {
            $query = $query->whereIn('meta_value->no', $neededCols);
        }
        
        $lookups    =  $query->select(
                            DB::raw(" meta_value->>'$.name' as name "),
                            'look_ups.*'
                        )
                        ->get();

        $index = 0;
        foreach ($lookups as $key => $lookup) 
        {
            $index = $key + 1;
            $cols .= ", T{$index}.{$lookup->display_key} as join_{$lookup->name} ";
            if(strpos($lookup->table, 'translation') !== false && $lang)  
            {
                $joins .= " LEFT JOIN {$lookup->table} as T{$index} ON T0.{$lookup->name} = T{$index}.{$lookup->store_key} AND T{$index}.locale = '{$lang}' ";
            } 
            else
            {
                $joins .= " LEFT JOIN {$lookup->table} as T{$index} ON T0.{$lookup->name} = T{$index}.{$lookup->store_key} ";
            }
        }


        if($translation) 
        {
            $translationTable = current($translation);
            $translationTableKey = key($translation);
            $index = $index + 1;
            if(count($translationColumns)) 
            {
                foreach ($translationColumns as $translationColumn) 
                {                    
                    $cols .= ", T{$index}.{$translationColumn->nme} ";
                }
            }
            $joins .= " LEFT JOIN {$translationTable} as T{$index} ON T0.{$pk} = T{$index}.{$translationTableKey} ";
        }

        $where = " WHERE T0.{$pk} = '{$primary}' ";

        if($translation && $lang) 
        {
            $where .= " AND T{$index}.locale = '{$lang}' ";
        }

        $result = DB::select("SELECT {$filters} {$cols} FROM {$tableName} as T0 {$joins} {$where} ");
        if(count($result) === 1) 
        {
            return response()->json($result[0]);
        }
        else
        {
            return response()->json((object) null);
        }
    }
}
