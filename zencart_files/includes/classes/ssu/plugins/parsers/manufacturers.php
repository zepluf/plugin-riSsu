<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: manufacturers.php 272 2009-11-09 17:34:36Z yellow1912 $
*/
/**
 * This class will try to parse manufacturers_id= in the query string into a nice looking name
 * It also takes care of parsing back such strings 
 *
 */
class manufacturersParser extends SSUParser{
	static $table			= TABLE_MANUFACTURERS;
	static $name_field 		= "manufacturers_name";
	static $id_field 		= "manufacturers_id";
	static $main_page 		= "index";
	static $identifier 		= "manufacturers";
	static $query_key 		= "manufacturers_id";
	static $is_main_page	= false;

	static function getStatic($name){
		return self::$$name;
	}
	
	/**
	 * This function is used to identify if the current page is a "manufacturer" page
	 * If so, it will update the main_page and parse the string into correct $_GET value
	 * @param array $params
	 * @param array $_get
	 * @return bool
	 */
	static function identifyPage(&$params, &$_get){
		if(self::identifyName($params[0])){
			$_get['main_page'] = self::getMainPage();
			self::updateGet($params[0], $_get);
			unset($params[0]);
			return true;
		}
		return false;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $page
	 * @param unknown_type $params
	 * @return unknown
	 */
	static function identifyPage2(&$page, $params){
		if($page == self::getMainPage() && self::identifyParam($params) !== false){
			self::$is_main_page = true;
			$page = '';
			return true;
		}
		return false;
	}
	
	/**
	 * This function identify if a string contains identifier
	 *
	 * @param string $string
	 * @return bool
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
		return strpos($string, self::$query_key.'=');
	}
	
	/**
	 * Enter description here...
	 *
	 * @return string
	 */
	static function getMainPage(){
		return self::$main_page;	
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $string
	 * @param unknown_type $_get
	 */
	static function updateGet($string, &$_get){
		$_get[self::$query_key] = self::getID($string, SSUConfig::registry('delimiters', 'id'));
	}
	
	/**
	 * Given the page, parameters, and languages, manipulates parameters array
	 * to get the desired SEO link type 
	 *
	 * @param string $page
	 * @param string array $params
	 * @param int $languages_id
	 * @param string $languages_code
	 */
	static function parseParam(&$_get, &$params, $languages_id, $languages_code){
		// if this function is called, it means that the array_search must return a valid pos, no need to check
		$pos = array_search(self::$query_key, $params);
		if(!empty($params[$pos+1])){
			if(self::$is_main_page){
				$params = array_merge(array(self::getName($params[$pos+1], $languages_id, $languages_code)), $params);
				unset($params[++$pos]);
			}
			else
				$params[$pos] = self::getName($params[$pos+1], $languages_id, $languages_code);
		}
		else 
			unset($params[$pos]);
		$id = $params[$pos+1];
		unset($params[$pos+1]); 
		return $id;
	}
	
	/**
	 * Given the id, languages, queries the name
	 *
	 * @param int $id
	 * @param int $languages_id
	 * @param string $languages_code
	 * @return string
	 */
	static function getName($id, $languages_id, $languages_code){
		return parent::getName($id, self::$id_field, self::$name_field, self::$table, SSUConfig::registry('identifiers', self::$identifier), self::$identifier, 0, $languages_code);
	}
}