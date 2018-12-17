<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;

class LookUpController extends Controller
{

    public function lookUp(Request $request)
    {
        if(array_key_exists('lang', $inputs) && $inputs['lang']) 
        {
            $lang = $inputs['lang'];
        }
        else
        {
            $lang = false;
        }
        $rdf     = $request->rdf;
        $lookup  = DB::table('look_ups')->find($rdf);

        $store_key      = $lookup->store_key;
        $display_key    = $lookup->display_key;
        $table          = $lookup->table;
        $query          = $lookup->query;

        $query = str_replace(':store_key', $store_key, $query);
        $query = str_replace(':display_key', $store_key, $query);
        $query = str_replace(':table', $table, $query);

        if($lang)
        {
            $query = str_replace(':condition', " WHERE locale = '{$lang}' ", $query);
        }
        else 
        {
            $query = str_replace(':condition', " ", $query);
        }
        $lookups = DB::select($query);
        return response()->json($lookups);
    }
}
