<?php

namespace App\Schema\QueryBuilder;

class QueryBuilder {

	public static function whereBuilder($conditions, $parent = '')
	{
		if(!$conditions)
	    {
	        return '';
	    }
	    $where = '';

	    foreach($conditions as $condition)
	    {
	        if(is_array($condition) && array_key_exists('logic', $condition)) 
	        {
	            $logic = $condition['logic'];
	            $result = self::whereBuilder($condition['cluse'], $logic);
	            $where .= " ( " . $result . " ) ";
	        }
	        else
	        {
	            $op 	= array_key_exists('op', $condition) ? $condition['op']  : 'LIKE';
	            $key 	= $condition['key'];
	            $value 	= $condition['value'];

	            if($op === 'LIKE') 
	            {
	                $value = "%".$value."%";
	            }

	            $where .= " T0.{$key} {$op} '{$value}' {$parent}";
	        }
	    }

	    $where = rtrim($where, 'AND');
	    $where = rtrim($where, 'OR');
	    return $where;
	}

}