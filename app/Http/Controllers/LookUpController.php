<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Schema\Translation;
use DB;

class LookUpController extends Controller
{

    public function lookUp(Request $request)
    {
        $lang   = ($request->has('lang') && $request->lang) ? $request->lang : false;
        $rdf    = $request->rdf;
        $relatedKey = $request->relatedKey;
        $relatedValue = $request->relatedValue;

        $lookup = DB::table('look_ups')->find($rdf);
        $store_key      = $lookup->store_key;
        $display_key    = $lookup->display_key;
        $table          = $lookup->table;
        $query          = $lookup->query;

        $query = str_replace(':store_key', $store_key, $query);
        $query = str_replace(':display_key', $display_key, $query);
        $query = str_replace(':table', $table, $query);

        if($lang AND Translation::isTranslatable($table))
        {
            if($relatedKey && $relatedValue) {
                $translationTable = $table;
                $pureTable = str_replace('_translation', '', $table);
                
                // should change better hard coding "id" for primary
                $query = "SELECT T0.{$store_key} as value, T0.{$display_key} as label FROM {$translationTable} as T0 LEFT JOIN {$pureTable} as T1 ON T0.{$store_key} = T1.id WHERE T1.{$relatedKey} = '{$relatedValue}' AND T0.locale = '{$lang}' ";
            } else {
                $query = str_replace(':condition', " WHERE locale = '{$lang}' ", $query);
            }
        }
        else 
        {
            if($relatedKey && $relatedValue) {
                $query = str_replace(':condition', " WHERE '{$relatedKey}' = '{$relatedValue}' ", $query);
            } else {
                $query = str_replace(':condition', " ", $query);
            }
        }
        $lookups = DB::select($query);
        return response()->json($lookups);
    }
}
