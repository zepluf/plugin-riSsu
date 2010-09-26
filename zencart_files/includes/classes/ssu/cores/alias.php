<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: alias.php 337 2010-06-27 06:32:47Z yellow1912 $
*/
class SSUAlias{
	// store all
	static  $aliases = array();
	static  $links = array();
	
	// store only enabled aliases
	static  $_aliases = array();
	static  $_links = array();
	
	static $no_search = array('link_alias'=>array(), 'link_url' => array());
	
	// Aliases needed to be queried on demand
	static function retrieveAliases(){
		if(!(isset($_SESSION['ssu_aliases_created_on']) && $_SESSION['ssu_aliases_created_on'] > (int)SSU_CACHE_RESET_TIME)){
			global $db;
			
			$aliases = $db->Execute('SELECT * FROM '.TABLE_LINKS_ALIASES. ' ORDER BY length(link_alias) DESC');
			while(!$aliases->EOF){
				self::$aliases[] = 	$aliases->fields['link_alias'];
				self::$links[] = 	$aliases->fields['link_url'];
		
				if($aliases->fields['status'] == 1){
					self::$_aliases[] = 	$aliases->fields['link_alias'];
					self::$_links[] = 	$aliases->fields['link_url'];
				}
				$aliases->MoveNext();	
			}
			$_SESSION['ssu_aliases']['created_on'] = time();
			self::$no_search = array('link_alias' => array(), 'link_url' => array());
		}
		else{
			SSUCache::read();
		}
	}
	
	// Aliases needed to be loaded on demand
	static function retrieveAliasesOnDemand($params, $field, $compare, $from, $to, $status=null){
		$params = explode('/',$params);
		foreach($params as $key => $value){
			$params[$key] = "/$value/";
			if(in_array($params[$key], self::$no_search[$field]))
				unset($params[$key]);
		}
		$elements_to_query = array_diff($params, self::$$compare);
		$id_list = array();
		if(count($elements_to_query) > 0)	{
			foreach($elements_to_query as $element){
				$element = zen_db_input($element);
				$conditions[] = "$field LIKE '%$element%' ";	
			}
			$conditions = implode(' OR ', $conditions);
			$query_string = 'SELECT DISTINCT link_url, link_alias, id FROM '.TABLE_LINKS_ALIASES." WHERE ($conditions)";
			$query_string .= !empty($status) ? " AND status = $status" : '';
			$query_string .= " ORDER BY length(link_alias) DESC";
			global $db;
			$alias_result = $db->Execute($query_string);
			while(!$alias_result->EOF){
				array_push(self::$$from, $alias_result->fields['link_url']);
				array_push(self::$$to, $alias_result->fields['link_alias']);
				
				
				$id_list[] = $alias_result->fields['id'];
				
				unset($elements_to_query[$alias_result->fields[$field]]);
				$alias_result->MoveNext();	
			}
			
			foreach ($elements_to_query as $element)
			self::$no_search[$field][] = $element;
		}
		
		return $id_list;
	}
	
	static function aliasToLink(&$params){
		$count = 0;
		self::retrieveAliasesOnDemand($params, 'link_alias', 'aliases', 'links', 'aliases');
		$params = trim(str_replace(self::$aliases, self::$links, "/$params/", $count), '/');
		return $count;
	}
	
	static function linkToAlias(&$params){
		$count = 0;
		self::retrieveAliasesOnDemand($params, 'link_url', '_links', '_links', '_aliases', 1);
		$params = trim(str_replace(self::$_links, self::$_aliases, "/$params/", $count), '/');
		return $count;
	}
	
	static function autoAlias($id, $name_field, $name, $_name){
		// if the alias happens to be the same with a define page, return.
		if(is_dir(DIR_WS_MODULES."pages/$_name"))
			return;
			
		global $db;
					
		// if we are generating aliases, make sure we use the product name without the attribute string
		// $name = current(explode(':', $name));
		$name = zen_db_input("/$name/");
    $_name = zen_db_input("/$_name/");
    $id = zen_db_input($id);
		
		// always update first
		$db->Execute("UPDATE ".TABLE_LINKS_ALIASES." SET link_url = '$name' WHERE referring_id='$id' AND link_alias='$_name' AND alias_type='$name_field'");
		
		// do we have any permanent link?
		$count = $db->Execute("SELECT count(*) as count FROM ".TABLE_LINKS_ALIASES." WHERE referring_id='$id' AND alias_type='$name_field' AND permanent_link = 1 AND STATUS = 1 LIMIT 1");
		if($count->fields['count'] > 0) return;
		
		// check if we already have this alias, then do nothing
		$count = $links_aliases = $db->Execute("SELECT count(*) as count FROM ".TABLE_LINKS_ALIASES." WHERE referring_id='$id' AND alias_type='$name_field' AND link_url = '$name' AND link_alias = '$_name' LIMIT 1");
		if($count->fields['count'] > 0) return;
		
		// check if the alias with the corresponding reffering_id and type is already there
		$links_aliases = $db->Execute("SELECT id, link_url, link_alias FROM ".TABLE_LINKS_ALIASES." WHERE referring_id='$id' AND alias_type='$name_field' AND status = '1'");
		if($links_aliases->RecordCount() > 0){
			while(!$links_aliases->EOF){
				// only if we dont have this exact key pair in the database yet
				if($links_aliases->fields['link_url'] == $name && $links_aliases->fields['link_alias'] != $_name){
					// disable the current link-alias
					$db->Execute("UPDATE ".TABLE_LINKS_ALIASES." SET status = '0' WHERE id='{$links_aliases->fields['id']}'");
					// add a new one in
					$db->Execute("INSERT INTO ".TABLE_LINKS_ALIASES." (link_url, link_alias, alias_type, referring_id) VALUES('$name', '$_name', '$name_field', '$id')");
					return;
				}
				$links_aliases->MoveNext();
			}
		}
		
		// check if we already have this link url, then we update referring id and type
		$links_aliases = $db->Execute("SELECT * FROM ".TABLE_LINKS_ALIASES." WHERE link_url='$name' LIMIT 1");
		if($links_aliases->RecordCount() > 0){
			// update the referring_id and alias_type
			if($links_aliases->fields['referring_id'] != $id && $links_aliases->fields['alias_type'] == $name_field)
				$db->Execute("UPDATE ".TABLE_LINKS_ALIASES." SET referring_id='$id' AND alias_type='$name_field' WHERE id = '{$links_aliases->fields['id']}'");
			return;
		}
		
		// otherwise insert the new alias
		$links_aliases = $db->Execute("SELECT COUNT(*) AS count FROM ".TABLE_LINKS_ALIASES." WHERE link_url='$name' OR link_alias ='$_name'");
		if($links_aliases->fields['count'] == 0)
			$db->Execute("INSERT INTO ".TABLE_LINKS_ALIASES." (link_url, link_alias, alias_type, referring_id) VALUES('$name', '$_name', '$name_field', '$id')");
		//
	}
	
	function insertCacheToDB($id_list, $pc_file_name, $alias_file_name){
		global $db;
		foreach ($id_list as $id){
			$db->Execute('INSERT INTO '.TABLE_SSU_ALIAS_CACHE."(links_aliases_id, file) VALUES($id, $pc_file_name, $alias_file_name)");
		}
	}
}