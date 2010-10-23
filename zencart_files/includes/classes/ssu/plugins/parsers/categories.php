<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: categories.php 268 2009-11-08 05:37:36Z yellow1912 $
*/
class categoriesParser extends SSUParser{
	static $table			= TABLE_CATEGORIES_DESCRIPTION;
	static $name_field 		= "categories_name";
	static $main_page 		= "index";
	static $identifier	 	= "categories";
	static $query_key 		= "cPath";
	static $is_main_page	= false;
	
	static function getStatic($name){
		return self::$$name;
	}
	
	static function identifyPage(&$params, &$_get){
		if(self::identifyName($params[0]) && productsParser::identifyName($params[1]) === false){
			$_get['main_page'] = self::getMainPage();
			self::updateGet($params[0], $_get);
			unset($params[0]);
			return true;
		}
		return false;
	}
	
	static function identifyPage2(&$page, $params){
		if($page == self::getMainPage() && self::identifyParam($params)!== false && productsParser::identifyParam($params) === false){
			self::$is_main_page = true;
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
		if (productsParser::identifyParam($string) !== false)
			return false;
		return strpos($string, self::$query_key.'=');
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
		$id = end(explode('_', $params[$pos+1]));
		// do not parse if this is a product page, leave the job
		if(productsParser::identifyName($_get['main_page'])){ 
			return $id;
		}
		
		if(!empty($params[$pos+1])){
			$params[$pos+1] = self::rebuildCpath($params[$pos+1]);
			if(isset($_get['cPath'])) $_get['cPath'] = $params[$pos+1];
			if(self::$is_main_page){
				$params = array_merge(array(self::getName($params[$pos+1], $languages_id, $languages_code)), $params);
				unset($params[++$pos]);
			}
			else
				$params[$pos] = self::getName($params[$pos+1], $languages_id, $languages_code);
		}
		else 
			unset($params[$pos]);
		unset($params[$pos+1]); 
		return $id;
	}
	
	static function rebuildCpath($cPath){
		// do not trust the passed cPath, always rebuild it
		$current_categories_id = self::getID($cPath, '_');
		$category_ids = array();
		zen_get_parent_categories($category_ids, (int)$current_categories_id);	
		$category_ids = array_reverse($category_ids);
		$category_ids[] = (int)$current_categories_id;
		return implode('_' , $category_ids);
	}
	
	static function getName($cPath, $languages_id, $languages_code){
		$cache_filename = self::buildFileName($cPath, $languages_code);
		if(($name = SSUCache::read($cache_filename, self::$identifier)) !== false)
			return $name;
		$current_categories_id = self::getID($cPath, '_');
		$category_ids = explode('_', $cPath);
		$cat_count = count($category_ids);
		
		$counter = $cat_count - SSUConfig::registry('configs', 'max_level');
		
		if($counter < 0) $counter = 0;
		
		$_name = array();
		// this may not be the best way to build the category name, but we do this once per cPath only
		while($counter<=($cat_count-1)){
			$category_ids[$counter] = (int)$category_ids[$counter];
			$sql_query = "SELECT categories_name FROM ".self::$table." WHERE categories_id ='".$category_ids[$counter]."' AND language_id= '$languages_id' LIMIT 1";
			$__name = self::getNameFromDB($sql_query, self::$name_field);
			// fall back to default language
			if(empty($__name) && $languages_id != 0){
				$sql_query = "SELECT categories_name FROM ".self::$table." WHERE categories_id ='".$category_ids[$counter]."' AND language_id = 1 LIMIT 1";
				$__name = self::getNameFromDB($sql_query, self::$name_field);
			}
			
			//if(empty($__name))
			//	$__name = self::$name_field;
				
			$_name[] = SSULanguage::parseName($__name, $languages_code);
			$counter++;
		}			
		
		if(empty($_name)) $_name = SSULanguage::parseName($name_field, $languages_code);
		
		if(SSUConfig::registry('configs', 'alias_status'))  {
			$_name = implode(SSUConfig::registry('configs', 'category_separator'), $_name);
			$name = str_replace(SSUConfig::registry('configs', 'category_separator'), SSUConfig::registry('delimiters', 'name'), $_name).SSUConfig::registry('identifiers', self::$identifier).$cPath;
		}
		else{
			$_name = implode(SSUConfig::registry('delimiters', 'name'), $_name);
			$name = $_name.SSUConfig::registry('identifiers', self::$identifier).$cPath;
		}
		// write to file EVEN if we get an empty content
		SSUCache::write($cache_filename, self::$identifier, $name);
		
		// write to link alias

		if(SSUConfig::registry('configs', 'auto_alias'))
			SSUAlias::autoAlias($current_categories_id, self::$identifier, $name, $_name);
		
		return $name;
			
		}
}