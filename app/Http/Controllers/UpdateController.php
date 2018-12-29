<?php

namespace App\Http\Controllers;
use App\Schema\Filter\Filter;
use App\Schema\Security\Security;
use App\Schema\Validation\Validation;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use Storage;

class UpdateController extends Controller
{

    public function update(Request $request)
    {
        $inputs = $request->all();
        $url    = $inputs['url'];
        $primaryValue = $inputs['primary'];

        $lang = (array_key_exists('lang', $inputs) && $inputs['lang']) ? $inputs['lang'] : false; 

        $schemaRecord = DB::table('schema')
            ->where('meta_value->url', $url)
            ->select(
                'meta_key',
                DB::raw(" meta_value->>'$.edt' as edit "),
                DB::raw(" meta_value->>'$.lst' as list "),
                DB::raw(" meta_value->>'$.pk' as pk "),
                DB::raw(" meta_value->>'$.trs' as translation ")
            )
            ->first();

        if(!$schemaRecord) return resposne('Not found.', 404);

        $tableName      = $schemaRecord->meta_key;
        $securityResult = Security::tableSecurity($tableName, 'update');

        if(!$securityResult['hasPermission']) 
        {
            return response('Permission deny', 403);
        }

        $primaryKey     = $schemaRecord->pk;
        $edit           = json_decode($schemaRecord->edit);
        $list           = json_decode($schemaRecord->list);
        $translation    = json_decode($schemaRecord->translation);

        $listColumnsArray    = Filter::getTableColumns($tableName, 'lst');
        $editColumns    = Filter::getTableColumns($tableName, 'edt');

        $listColumns = [];
        foreach ($listColumnsArray as $listColumn) 
        {
            $listColumns[] = $listColumn->nme;
        }

        $row = DB::table($tableName)
                ->where($primaryKey, $primaryValue)
                ->first();
        if(!$row) return resposne('Not found.', 404);


        $validationError = [];
        $shouldUpdate = [];
        $shouldUpdateInTranslation = [];
        $shouldShow = [];
        $status = 200;
        foreach ($editColumns as $key => $editColumn) 
        {
            $errors = Validation::checkValidation($editColumn, $inputs);
            if(count($errors)) 
            {
                $validationError = array_merge($validationError, $errors);
                $status = 422;
                continue;
            }

            if($request->hasFile($editColumn->nme))
            {
                $path = $request->{$editColumn->nme}->store('public/images/' . $tableName);
                if(Storage::exists($row->{$editColumn->nme})) 
                {
                    Storage::delete($row->{$editColumn->nme});
                }

                if(!property_exists($editColumn, 'trs')) 
                {
                    $shouldUpdate[$editColumn->nme] = $path;
                } 
                else 
                {
                    $shouldUpdateInTranslation[$editColumn->nme] = $path;
                }
            }
            else if($request->has($editColumn->nme))
            {
                if($editColumn->cnt === 'pas') 
                {
                    $shouldUpdate[$editColumn->nme] = bcrypt($inputs[$editColumn->nme]);
                }
                else if(!property_exists($editColumn, 'trs')) 
                {
                    $shouldUpdate[$editColumn->nme] = $inputs[$editColumn->nme];
                } 
                else 
                {
                    $shouldUpdateInTranslation[$editColumn->nme] = $inputs[$editColumn->nme];
                }
            }

            if (in_array($editColumn->nme, $listColumns)) 
            {
                if(array_key_exists($editColumn->nme, $shouldUpdate))
                {
                    $shouldShow[$editColumn->nme] = $shouldUpdate[$editColumn->nme];
                }
                else if(array_key_exists($editColumn->nme, $shouldUpdateInTranslation)) 
                {
                    $shouldShow[$editColumn->nme] = $shouldUpdateInTranslation[$editColumn->nme];
                }
            }
        }

        if($status === 200)
        {
            $updatedDateTime = Carbon::now();
            $shouldUpdate['updated_at'] = $updatedDateTime;
            DB::table($tableName)
                ->where($primaryKey, $primaryValue)
                ->update($shouldUpdate);

            if($lang && count($shouldUpdateInTranslation) && key($translation) && current($translation)) 
            {
                $query = DB::table(current($translation))
                ->where(key($translation), $primaryValue)
                ->where('locale', $lang);

                if($query->exists()) 
                {
                    $query->update($shouldUpdateInTranslation);
                }
                else
                {
                    $shouldUpdateInTranslation['locale'] = $lang;
                    $shouldUpdateInTranslation[key($translation)] = $primaryValue;
                    DB::table(current($translation))->insert($shouldUpdateInTranslation);
                }
            }

            if (in_array('updated_at', $listColumns)) 
            {
                $shouldShow['updated_at'] = $updatedDateTime->toDateTimeString();
            }
        }

        return response()->json([
            'result'    =>  ($status === 422) ? $validationError : $shouldShow,
            'status'    =>  $status
        ], $status);
    }
}
