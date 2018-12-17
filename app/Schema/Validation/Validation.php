<?php

namespace App\Schema\Validation;
use DB;

class Validation {

	public static function checkValidation($column, $inputs)
	{
		$label = $column->lbl;

		if(!property_exists($column, 'vld')) 
		{
			return [];
		}

    	$validations = (array) $column->vld;
    	
    	if(!count($validations)) 
    	{
    		return [];
    	}

    	if(array_key_exists($column->nme, $inputs)) 
	    {
	        $value = trim($inputs[$column->nme]);
	    }
	    else 
	    {
	        $value = null;
	    }

	    $msg = DB::select('SELECT * FROM schema_msg');
	    $errors = [];
	    $required = 'required';
	    $numeric = 'numeric';
	    $minVal = 'minVal';
	    $maxVal = 'maxVal';
	    $minLen = 'minLen';
	    $maxLen = 'maxLen';
	    $minRel = 'minRel';
	    $maxRel = 'maxRel';


	    if(array_key_exists($required, $validations)) 
	    {
	        if(!($value !== '' AND isset($value))) 
	        {
	            $errors[] = sprintf($msg[0]->meta_value, $label);
	        }
	    }

	    if(array_key_exists($numeric, $validations)) 
	    {
	        if(!is_numeric($value)) 
	        {
	            $errors[] = sprintf($msg[1]->meta_value, $label);
	        }
	    }

	    if(array_key_exists($minVal, $validations)) 
	    {
	        if(!((int)$value > (int)$validations[$minVal])) 
	        {
	            $errors[] = sprintf($msg[2]->meta_value, $label ,(int)$validations[$minVal]);   
	        }
	    }

	    if(array_key_exists($maxVal, $validations)) 
	    {
	        if(!((int)$value < (int)$validations[$maxVal]))
	        {
	            $errors[] = sprintf($msg[3]->meta_value, $label ,(int)$validations[$maxVal]);
	        }
	    }

	    if(array_key_exists($minLen, $validations)) 
	    {
	        if(!(strlen($value) > (int)$validations[$minLen]))
	        {
	            $errors[] = sprintf($msg[4]->meta_value, $label ,(int)$validations[$minLen]);
	        }
	    }

	    if(array_key_exists($maxLen, $validations)) 
	    {
	        if(!(strlen($value) < (int)$validations[$maxLen]))
	        {
	            $errors[] = sprintf($msg[5]->meta_value, $label ,(int)$validations[$maxLen]);
	        }
	    }

	    if(array_key_exists($minRel, $validations)) 
	    {
	        $url = $inputs['url'];
	        $minRelNo = $validations[$minRel];

	        if(is_date($value) && $minRelNo == 'now()') 
	        {
	            if( !(strtotime($value) > (time() - (60*60*24)) ))
	            {
	                $errors[] = sprintf($msg[8]->meta_value, $label);
	            }
	        }
	        else 
	        {
	            $result = DB::select("SELECT s1.meta_value->>'$.name' as name, s1.meta_value->>'$.label' as label FROM `schema` as s1 JOIN `schema` as s2 WHERE s1.meta_value->>'$.no' = '{$minRelNo}' AND s2.meta_value->>'$.url' = '{$url}' AND s2.meta_key = s1.meta_value->>'$.table' ");

	            if(count($result) === 1)
	            {
	                $minRelName = $result[0]->name;
	                $minRelLable = $result[0]->label;
	                if(array_key_exists($minRelName, $inputs)) 
	                {
	                    $minRelValue = $inputs[$minRelName];
	                    if(is_numeric($minRelValue))
	                    {
	                        if(!((int)$value > (int)$minRelValue)) 
	                        {
	                            $errors[] = sprintf($msg[6]->meta_value, $label, $minRelLable);
	                        }
	                    } 
	                    elseif(is_date($value) && is_date($minRelValue)) 
	                    {
	                        if(!(strtotime($value) > strtotime($minRelValue)))
	                        {
	                            $errors[] = sprintf($msg[6]->meta_value, $label, $minRelLable);
	                        }
	                    }
	                }
	            }
	        }

	    }

	    if(array_key_exists($maxRel, $validations)) 
	    {
	        $url = $inputs['url'];
	        $maxRelNo = $validations[$maxRel];

	        if(is_date($value) && $maxRelNo == 'now()') 
	        {
	            if(!(strtotime($value) < (time() - (60*60*24)) ))
	            {
	                $errors[] = sprintf($msg[9]->meta_value, $label);
	            }
	        }
	        else
	        {
	            $result = DB::select("SELECT s1.meta_value->>'$.name' as name, s1.meta_value->>'$.label' as label FROM `schema` as s1 JOIN `schema` as s2 WHERE s1.meta_value->>'$.no' = '{$maxRelNo}' AND s2.meta_value->>'$.url' = '{$url}' AND s2.meta_key = s1.meta_value->>'$.table' ");
	            
	            if(count($result) === 1)
	            {
	                $maxRelName = $result[0]->name;
	                $maxRelLable = $result[0]->label;
	                if(array_key_exists($maxRelName, $inputs)) 
	                {
	                    $maxRelValue = $inputs[$maxRelName];
	                    if(is_numeric($maxRelValue))
	                    {
	                        if(!((int)$value < (int)$maxRelValue)) 
	                        {
	                            $errors[] = sprintf($msg[7]->meta_value, $label, $maxRelLable);
	                        }
	                    }
	                    elseif(is_date($value) && is_date($maxRelValue)) 
	                    {
	                        if(!(strtotime($value) < strtotime($maxRelValue)))
	                        {
	                            $errors[] = sprintf($msg[7]->meta_value, $label, $maxRelValue);
	                        }
	                    }
	                }
	            }
	        }
	    }

	    return $errors;

	}

	function is_date($str)
	{
	    $str = str_replace('/', '-', $str);     
	    $stamp = strtotime($str);
	    if (is_numeric($stamp)){  
	       $month = date('m', $stamp); 
	       $day   = date('d', $stamp); 
	       $year  = date('Y', $stamp); 
	       return checkdate($month, $day, $year); 
	    }  
	    return false; 
	}
}