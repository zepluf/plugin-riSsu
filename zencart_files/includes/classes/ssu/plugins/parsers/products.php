<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: products.php 271 2009-11-09 04:27:13Z yellow1912 $
*/
class productsParser extends SSUParser{
	static $table			= TABLE_PRODUCTS_DESCRIPTION;
	static $name_field 		= "products_name";
	static $id_field 		= "products_id";
	static $main_page 		= "product_info";
	static $identifier 		= "products";
	static $query_key 		= "products_id";
			
	// anxiously waiting for php 5.3
	static function getStatic($name){
		return self::$$name;
	}
	
	static function identifyPage(&$params, &$_get){
		if(isset($params[1]) && categoriesParser::identifyName($params[0]) && self::identifyName($params[1])){
			categoriesParser::updateGet($params[0], $_get);
			self::updateGet($params[1], $_get);
			$_get['main_page'] = self::getMainPage();
			unset($params[0]);
			unset($params[1]);
			return true;
		}
		elseif(self::identifyName($params[0])){
			$_get['main_page'] = self::getMainPage();
			self::updateGet($params[0], $_get);
			unset($params[0]);
			return true;
		}
		return false;
	}

	static function identifyPage2(&$page){
		foreach(SSUConfig::registry('identifiers', self::$identifier) as $main_page => $identifier){
			if($page == $main_page){		
				self::$main_page = $main_page;	
				$page = '';
				return true;
			}
		}
		return false;
	}
	
	/*
	 * This function identify if a string contains product identifier
	 */
	static function identifyName($string){
		foreach(SSUConfig::registry('identifiers', self::$identifier) as $main_page => $identifier)
			if(strpos($string, $identifier) !== false){
			self::$main_page = $main_page;
			return true;
			}
		
		return false;
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
	
	static function getMainPage(){
		return self::$main_page;	
	}
	
	static function updateGet($string, &$_get){
		// urldecode is used as a hot fix for the problem with attribute id attached to product id
		// TODO: find a better way to resolve this
		$_get[self::$query_key] = urldecode(self::getID($string, SSUConfig::registry('delimiters', 'id')));
	}
	
	static function parseParam(&$_get, &$params, $languages_id, $languages_code){
		$pos = array_search(self::$query_key, $params);
		$products_id = 0;
		if(!empty($params[$pos+1])){
			$products_id = (int)$params[$pos+1];
			
			// recalculate 'page', just in case the product_type passed is wrong
			if(self::getMainPage() == $_get['main_page'])
				self::$main_page = $_get['main_page'] = zen_get_info_page($products_id);
			
			$cPos  = array_search(categoriesParser::$query_key, $params);
			if(self::getMainPage() == $_get['main_page'] || $cPos !== false){
				// we want to make sure the order is correct, categories first then product
				unset($params[$pos]);
				unset($params[$pos+1]);
				
				if($cPos !== false){
					$_get['cPath'] = $cPath = self::getProductPath($products_id, $params[$cPos+1]);
					unset($params[$cPos]);
					unset($params[$cPos+1]);
				}
				else 
					$_get['cPath'] = $cPath = self::getProductPath($products_id, 0);
				
				$temp_params[0] = categoriesParser::getName($cPath, $languages_id, $languages_code);
				$temp_params[1] = self::getName($products_id, $languages_id, $languages_code);
				$params = array_merge($params,$temp_params);
			}
			else{
				$params[$pos] = self::getName($products_id, $languages_id, $languages_code);
				unset($params[$pos+1]);
			}
		}
		else 
			unset($params[$pos]);
		return $products_id;
	}
	
	static function getName($id, $languages_id, $languages_code){
		$identifiers 	= SSUConfig::registry('identifiers', self::$identifier);
		return parent::getName($id, self::$id_field, self::$name_field, self::$table, $identifiers[self::$main_page], self::$identifier, $languages_id, $languages_code, 'language_id');
	}
	
	static function getProductPath($products_id, $cPath) {    
		global $db;
		$categories_id = self::getID($cPath, '_');
		$category_query = "select p2c.categories_id, p.master_categories_id
		                   from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
		                   where p.products_id = '" . $products_id . "'
		                   and p.products_id = p2c.products_id 
		                   and (p.master_categories_id = '$categories_id' or p2c.categories_id='$categories_id') limit 1";
		
		$category = $db->Execute($category_query);
		
		// fall back if needed to
		if ($category->RecordCount() == 0){
		 	$category_query = "select p.master_categories_id
		 						from " . TABLE_PRODUCTS . " p 
		 						where p.products_id = '" . $products_id . "' limit 1";
		 	$category = $db->Execute($category_query);
		 	if ($category->RecordCount() > 0)
		 		$categories_id = $category->fields['master_categories_id'];
		}
		
		$cPath = "";
		$categories = array();
		zen_get_parent_categories($categories, $categories_id);
		
		$categories = array_reverse($categories);
		
		$cPath = implode('_', $categories);
		
		if (zen_not_null($cPath)) $cPath .= '_';
		$cPath .= $categories_id;
		
		return $cPath;
	}	
}