<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: ssu_link_alias.php 308 2010-02-13 06:48:28Z yellow1912 $
*/
if (!function_exists('json_encode'))
{
  function json_encode($a=false)
  {
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a))
    {
      if (is_float($a))
      {
        // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }

      if (is_string($a))
      {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      }
      else
        return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a))
    {
      if (key($a) !== $i)
      {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList)
    {
      foreach ($a as $v) $result[] = json_encode($v);
      return '[' . join(',', $result) . ']';
    }
    else
    {
      foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
      return '{' . join(',', $result) . '}';
    }
  }
}

require('includes/application_top.php');
require_once(DIR_WS_CLASSES.'ssu.php');
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
function parse_name($name){
	$name = trim($name, ' /');
	$name = "/$name/";
	return $name;
}

if($_REQUEST['action']=='edit'){
	$new_data = array('link_url' 		=> 'parse_name',
										'link_alias' 	=> 'parse_name',
										'status' 			=> '',
										'permanent_link' 	=> ''
										);
	$call_backs = array('link_url' 		=> 'parse_name',
										'link_alias' 	=> 'parse_name');
										
	foreach($new_data as $k => $v){
		if(isset($_POST[$k]) && !empty($_POST[$k])){
			$new_data[$k] = $_POST[$k];
			if(isset($call_backs[$k]))
				$new_data[$k] = $call_backs[$k]($new_data[$k]);
		}
		//else 
		//	unset($new_data[$k]);
	}
	
	switch($_POST['oper']){
		case 'add':
			zen_db_perform(TABLE_LINKS_ALIASES, $new_data, 'insert');
			SSUManager::resetCacheTimer();
		break;
		
		case 'edit':
			zen_db_perform(TABLE_LINKS_ALIASES, $new_data, 'update', "id = '{$_POST['id']}'");
			SSUManager::removeCache($_POST['id']);
		break;
		
		case 'del';
			$db->Execute('DELETE FROM '.TABLE_LINKS_ALIASES." WHERE id IN ({$_POST['id']})");
			SSUManager::removeCache($_POST['id']);
		break;
	}		
	$response = array('affected_row_count' => mysql_affected_rows($db->link));
}
else{
	$page = $_REQUEST['page'];  // get the requested page
	$limit = $_REQUEST['rows']; // get how many rows we want to have into the grid
	$sidx = $_REQUEST['sidx']; // get index row - i.e. user click to sort
	$sord = $_REQUEST['sord']; // get the direction
	if(!$sidx) $sidx =1;
	
	// connect to the database
	$alias_count = $db->Execute('SELECT COUNT(*) AS count FROM '.TABLE_LINKS_ALIASES);
	
	if($alias_count->fields['count'] >0 ) {
	    $total_pages = ceil($alias_count->fields['count']/$limit);
	} else {
	    $total_pages = 0;
	}
	
	if ($page > $total_pages) $page=$total_pages;
	$start = $limit*$page - $limit; // do not put $limit*($page - 1)
	if ($start<0) $start = 0;
	
	$search_query = 'SELECT * FROM '.TABLE_LINKS_ALIASES;
	//page=1&rows=25&sidx=id&sord=desc&nd=1232788016273&_search=true&searchField=id&searchOper=eq&searchString=aa
	if(isset($_GET['_search'])){
		$searchField = $_GET['searchField'];
		$searchString = $_GET['searchString'];
		$op = '';
		switch($_GET['searchOper']){
			case 'eq':
				$op = '=';
			break;
			case 'cn':
				$op = 'LIKE';
				$searchString = "%$searchString%";
			break;
		}
		if(!empty($searchField) && (strlen($searchString) > 0) && !empty($op))
			$search_query .= " WHERE $searchField $op '$searchString'";
	}
	
	$search_query .= " ORDER BY ".$sidx." ".$sord. " LIMIT ".$start." , ".$limit;
		
	$aliases = $db->Execute($search_query);
	
	// Construct the json data
	$response->page = $page; // current page
	$response->total = $total_pages; // total pages
	$response->records = $alias_count->fields['count']; // total records
	
	while(!$aliases->EOF) {
		$status='Disabled';
		$permanent='No';
		if($aliases->fields['status']) $status='Enabled';
		if($aliases->fields['permanent_link']) $permanent='Yes';
	    $response->rows[]=
	    	array('id'		=>	$aliases->fields['id'],
	    				'cell'	=>	array($aliases->fields['id'],$aliases->fields['link_url'],$aliases->fields['link_alias'],/*$aliases->fields['languages_id'],*/$status, $permanent)
	    				);
	    $aliases->MoveNext();
	} 
}
echo json_encode($response);