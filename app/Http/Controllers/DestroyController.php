<?php

namespace App\Http\Controllers;
use App\Schema\Security\Security;
use Illuminate\Http\Request;
use DB;

class DestroyController extends Controller
{
    
    public function destroy(Request $request)
    {
        $inputs  = $request->all();

        if(!(array_key_exists('url', $inputs) && array_key_exists('primary', $inputs))) 
        {
            return response()->json([
                'status'    =>  422,
                'result'   =>  'خطایی رخ داده است'
            ]);
        }

        $lang = (array_key_exists('lang', $inputs) && $inputs['lang']) ? $inputs['lang'] : false; 

        $url     = $inputs['url'];
        $primary = $inputs['primary'];

        try 
        {
            $schemaRecord = DB::table('schema')
            ->where('meta_value->url', $url)
            ->select('meta_key as name', 'meta_value as meta')
            ->first();

            if(!$schemaRecord) return resposne('Not found.', 404);
            $securityResult = Security::tableSecurity($schemaRecord->name, 'delete');
            if(!$securityResult['hasPermission']) 
            {
                return response()->json([
                    'status'    =>  403,
                    'result'    =>  'شما اجازه انجام این عملیات را ندارید'
                ]);
            }

            $meta = json_decode($schemaRecord->meta);
            if(property_exists($meta, 'trs') && $lang) 
            {
                $translation = $meta->trs;
                DB::table(current($translation))
                ->where('locale', $lang)
                ->where(key($translation), $primary)
                ->delete();
            }
            else
            {
                DB::table($schemaRecord->name)->delete($primary);
            }

            return response()->json([
                'status'    =>  200,
                'result'   =>  'عملیات با موفقیت انجام شد'
            ]);
        } 
        catch (Exception $e) 
        {
            return response()->json([
                'status'    =>  500,
                'result'   =>  'خطایی رخ داده است'
            ]);
        }        
    }
    
}
