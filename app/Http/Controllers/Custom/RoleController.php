<?php

namespace App\Http\Controllers\Custom;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $primary = $request->primary;
        $result = DB::select("SELECT * FROM `schema` as s1 LEFT JOIN `schema_actions` as sa1 ON sa1.schema_id = s1.id WHERE sa1.role_id = '$primary' ");
        return response()->json($result);
    }

    public function update(Request $request)
    {
        $perm       = $request->perm;
        $condition  = $request->condition;
        $schema_id  = $request->schema_id;
        $role_id    = $request->role_id;
        $result     = DB::statement("UPDATE `schema_actions` SET perm='$perm', `condition`='$condition' WHERE schema_id='$schema_id' AND role_id='$role_id' ");
        return response()->json($result);
    }
}
