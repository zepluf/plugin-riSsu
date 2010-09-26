<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: pages.php 249 2009-09-10 04:27:39Z yellow1912 $
*/
class pagesParser extends SSUParser{
	static $table				= TABLE_EZPAGES;
	static $name_field 	= "pages_title";
	static $id_field 		= "pages_id";
	static $main_page 	= "page";
	static $identifier 	= "pages";
	static $query_key 	= "id";

	static function getStatic($name){
		return self::$$name;
	}
	
	static function identifyPage(&$params, &$_get){
		if(self::identifyName($params[0])){
			$_get['main_page'] = self::getMainPage();
			self::updateGet($params[0], $_get);
			unset($params[0]);
			return true;
		}
		return false;
	}

	static function identifyPage2(&$page){
		if($page == self::getMainPage()){
			$page = '';
			return true;
		}
		return false;
	}
	
	/*
	 * This function identify if a string contains category identifier
	 */
	static function identifyName($string){
		return parent::identifyName($string, SSUConfig::registry('identifiers', self::$identifier)); 
	}
	
	/*
	 * This function identify if a query string matches $query_key
	 */
	static function identifyQuery($string){
		return parent::identifyQuery($string, self::$query_key); 
	}
	
	static function identifyParam($string){
		return false;
	}
	
	static function getMainPage(){
		return self::$main_page;	
	}
	
	static function updateGet($string, &$_get){
		$_get[self::$query_key] = self::getID($string, SSUConfig::registry('delimiters', 'id'));
	}
	
	static function parseParam(&$_get, &$params, $languages_id, $languages_code){
		// if this function is called, it means that the array_search must return a valid pos, no need to check
		$pos = array_search(self::$query_key, $params);
		if(!empty($params[$pos+1]))
			$params[$pos] = self::getName($params[$pos+1], $languages_id, $languages_code);
		else 
			unset($params[$pos]);
		$id = $params[$pos+1];
		unset($params[$pos+1]); 
		return $id;
	}
	
	static function getName($id, $languages_id, $languages_code){
		if(defined('TABLE_EZPAGES_TEXT')) self::$table = TABLE_EZPAGES_TEXT;
		return parent::getName($id, self::$id_field, self::$name_field, self::$table, SSUConfig::registry('identifiers', self::$identifier), self::$identifier, $languages_id, $languages_code);
	}
}