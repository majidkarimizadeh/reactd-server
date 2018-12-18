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

	public static function whereBuilderOnTranslation($conditions, $parent = '', $translationColumns = [], $index = 0)
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
	            $result = self::whereBuilderOnTranslation($condition['cluse'], $logic, $translationColumns, $index);
	            $where .= " ( " . $result . " ) ";
	        }
	        else
	        {
	            $op 	= array_key_exists('op', $condition) ? $condition['op']  : 'LIKE';
	            $key 	= $condition['key'];
	            $value 	= $condition['value'];

	            if(trim($op) === 'LIKE') 
	            {
	                $value = "%".$value."%";
	            }

	            if(in_array($key, $translationColumns))
	            {
	            	$where .= " T{$index}.{$key} {$op} '{$value}' {$parent}";
	            }
	            else
	            {
	            	$where .= " T0.{$key} {$op} '{$value}' {$parent}";
	            }

	        }
	    }

	    $where = rtrim($where, 'AND');
	    $where = rtrim($where, 'OR');
	    return $where;
	}
}