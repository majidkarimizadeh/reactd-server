<?php

namespace App\Schema\Security;
use DB;

class Security
{
    private static $permissions = [
        'select'    =>  8,
        'insert'    =>  4,
        'update'    =>  2,
        'delete'    =>  1
    ];

    public static function menuSecurity($menus, $parent = null)
    {
        $user   = auth()->user();
        $roles = $user->roles()->pluck('id')->toArray();
        
        $actions = DB::table('schema_actions')
        ->join('schema', 'schema_actions.schema_id', '=', 'schema.id')
        ->where('schema.meta_value->tbl', null)
        ->whereIn('schema_actions.role_id', $roles)
        ->select(
            DB::raw('schema.meta_value->>"$.url" AS url'), 
            'schema.meta_key',
            'schema_actions.perm', 
            'schema_actions.role_id'
        )
        ->get();

        $items = [];
        $neededPermission = self::$permissions['select'];
        foreach ($menus as $menu) 
        {
            if(array_key_exists('itm', $menu)) 
            {
                $items[] = self::menuSecurity($menu['itm'], $menu);
            }
            else
            {
                foreach ($actions as $action) 
                {
                    if( array_key_exists('url', $menu) && 
                        $action->url === $menu['url'] &&
                        ($action->perm & $neededPermission) === $neededPermission) 
                    {
                        $items['itm'][] = $menu;
                    }
                }

                if(!is_null($parent) && array_key_exists('itm', $items))
                {
                    $items['lbl'] = array_key_exists('lbl', $parent) ? $parent['lbl'] : '' ;
                    $items['url'] = array_key_exists('url', $parent) ? $parent['url'] : '' ;
                    $items['icn'] = array_key_exists('icn', $parent) ? $parent['icn'] : '' ;
                }

            }
        }
        return $items;
    }

    public static function tableSecurity($tableName, $permission)
    {
        $userPermission = [
            'select'    =>  true,
            'insert'    =>  true,
            'update'    =>  true,
            'delete'    =>  true
        ];

        $user   = auth()->user();
        $roles  = $user->roles()->pluck('id')->toArray();

        $actions    =   DB::table('schema_actions')
                            ->join('schema', 'schema_actions.schema_id', '=', 'schema.id')
                            ->where('schema.meta_key', $tableName)
                            ->whereIn('schema_actions.role_id', $roles)
                            ->select(
                                DB::raw('schema.meta_value->>"$.url" AS url'), 
                                'schema.meta_key',
                                'schema_actions.perm', 
                                'schema_actions.role_id'
                            )
                            ->get();

        $neededPermission = self::$permissions[$permission];

        if($permission === 'select') 
        {
            foreach ($actions as $action) 
            {
                foreach (self::$permissions as $key => $value) 
                {
                    $userPermission[$key] = ($userPermission[$key] AND (($action->perm & $value) === $value));
                }
            }
        }

        foreach ($actions as $action) 
        {
            if(($action->perm & $neededPermission) !== $neededPermission)
            {
                return [
                    'hasPermission'   =>  false,
                    'permission'      =>  $userPermission
                ];
            }
        }

        return [
            'hasPermission'     =>  true,
            'permission'        =>  $userPermission
        ];
    }

    public static function detailSecurity($detailsName)
    {
        $details = [];

        $user   = auth()->user();
        $roles  = $user->roles()->pluck('id')->toArray();
        
        $actions    =   DB::table('schema_actions')
                            ->join('schema', 'schema_actions.schema_id', '=', 'schema.id')
                            ->where('schema.meta_value->tbl', null)
                            ->whereIn('schema.meta_key', $detailsName)
                            ->whereIn('schema_actions.role_id', $roles)
                            ->select('schema.meta_value', 'schema_actions.perm')
                            ->get();

        $neededPermission = self::$permissions['select'];
        foreach ($actions as $action) 
        {
            if( ($action->perm & $neededPermission) === $neededPermission ) 
            {
                $details[] = $action->meta_value;
            }
        }

        return $details;
    }
}
