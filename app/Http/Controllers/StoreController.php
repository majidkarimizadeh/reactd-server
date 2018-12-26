<?php

namespace App\Http\Controllers;
use App\Schema\Filter\Filter;
use App\Schema\Security\Security;
use App\Schema\Validation\Validation;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use File;

class StoreController extends Controller
{
    
    public function store(Request $request)
    {
        $inputs = $request->all();
        $url    = $inputs['url'];

        $lang = (array_key_exists('lang', $inputs) && $inputs['lang']) ? $inputs['lang'] : false; 

        $schemaRecord = DB::table('schema')
            ->where('meta_value->url', $url)
            ->select(
                DB::raw(" meta_value->>'$.nme' as name "),
                DB::raw(" meta_value->>'$.crt' as crt "),
                DB::raw(" meta_value->>'$.lst' as list "),
                DB::raw(" meta_value->>'$.pk' as pk "),
                DB::raw(" meta_value->>'$.trs' as translation ")
            )
            ->first();

        if(!$schemaRecord) return resposne('Not found.', 404);

        $tableName      = $schemaRecord->name;
        $securityResult = Security::tableSecurity($tableName, 'insert');

        if(!$securityResult['hasPermission']) 
        {
            return response('Permission deny', 403);
        }

        $primaryKey     = $schemaRecord->pk;
        $create         = json_decode($schemaRecord->crt);
        $list           = json_decode($schemaRecord->list);
        $translation    = json_decode($schemaRecord->translation);

        $listColumnsArray = Filter::getTableColumns($tableName, 'lst');
        $createColumns  = Filter::getTableColumns($tableName, 'crt');

        $listColumns = [];
        foreach ($listColumnsArray as $listColumn) 
        {
            $listColumns[] = $listColumn->nme;
        }

        $validationError = [];
        $shouldInsert = [];
        $shouldInsertInTranslation = [];
        $shouldShow = [];
        $status = 200;

        foreach ($createColumns as $key => $createColumn) 
        {
            $errors = Validation::checkValidation($createColumn, $inputs);
            if(count($errors)) 
            {
                $validationError = array_merge($validationError, $errors);
                $status = 422;
                continue;
            }

            if($request->hasFile($createColumn->nme))
            {
                $path = $request->{$createColumn->nme}->store('images/' . $tableName);
                if(!property_exists($createColumn, 'trs')) 
                {
                    $shouldInsert[$createColumn->nme] = $path;
                } 
                else 
                {
                    $shouldInsertInTranslation[$createColumn->nme] = $path;
                }
            }
            else if($request->has($createColumn->nme))
            {
                if($createColumn->cnt === 'pas') 
                {
                    $shouldInsert[$createColumn->nme] = bcrypt($inputs[$createColumn->nme]);
                }
                else if(!property_exists($createColumn, 'trs')) 
                {
                    $shouldInsert[$createColumn->nme] = $inputs[$createColumn->nme];
                } 
                else 
                {
                    $shouldInsertInTranslation[$createColumn->nme] = $inputs[$createColumn->nme];
                }
            }
            if (in_array($createColumn->nme, $listColumns)) 
            {
                if(array_key_exists($createColumn->nme, $shouldInsert)) 
                {
                    $shouldShow[$createColumn->nme] = $shouldInsert[$createColumn->nme];
                } 
                else if(array_key_exists($createColumn->nme, $shouldInsertInTranslation)) 
                {
                    $shouldShow[$createColumn->nme] = $shouldInsertInTranslation[$createColumn->nme];
                }
            }
        }

        if($status === 200)
        {
            $insertedDateTime = Carbon::now();
            $shouldInsert['updated_at'] = $shouldInsert['created_at'] = $insertedDateTime;
            $insertedRow = DB::table($tableName)->insertGetId($shouldInsert);

            if($lang && count($shouldInsertInTranslation) && key($translation) && current($translation)) 
            {
                $shouldInsertInTranslation['locale'] = $lang;
                $shouldInsertInTranslation[key($translation)] = $insertedRow;
                DB::table(current($translation))->insert($shouldInsertInTranslation);
            }

            if (in_array('created_at', $listColumns)) 
            {
                $shouldShow['created_at'] = $insertedDateTime->toDateTimeString();
            }

            if (in_array('updated_at', $listColumns)) 
            {
                $shouldShow['updated_at'] = $insertedDateTime->toDateTimeString();
            }

            if (in_array($primaryKey, $listColumns)) 
            {
                $shouldShow[$primaryKey] = $insertedRow;
            }
        }
        return response()->json([
            'result'    =>  ($status === 422) ? $validationError : $shouldShow,
            'status'    =>  $status
        ], $status);

    }


}
