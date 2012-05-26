<?php
if(!function_exists('mb_str_replace')){
	function mb_str_replace($search, $replace, $subject)
	{
	$size = mb_strlen($subject, 'UTF-8');
	
	if(!is_array($search))
	{
	$search = array($search);
	}
	
	for($i = 0; $i < sizeOf($search); $i++)
	{
	for($j = 0; $j < $size; $j++)
	{
	$ch = mb_substr($subject, $j, 1, 'UTF-8');
	
	if($ch == $search[$i])
	{
	$subject = mb_substr($subject, 0, $j, 'UTF-8').$replace[$i].mb_substr($subject, $j + 1, $size - $j, 'UTF-8');
	}
	}
	}
	
	return $subject;
	}
}