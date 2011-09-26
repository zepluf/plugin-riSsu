<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: parser.php 339 2010-08-09 02:24:51Z yellow1912 $
*/
class SSUParser{
    protected static $__CLASS__ = __CLASS__;
 		
 		/**
     * Returns the classname of the child class extending this class
     *
     * @return string The class name
     */
    /*protected static function getClass() {
        $implementing_class = self::$__CLASS__;
        $original_class = __CLASS__;

        if ($implementing_class === $original_class)
            throw new Exception("You MUST provide a <code>protected static \$__CLASS__ = __CLASS__;</code> statement in your Singleton-class!");
      
        return $implementing_class;
    } 
      */ 
    /*
	 * This function identify if the current page is our main page.
	 */
//	static protected function identifyPage($uri_parts){
//		return self::identifyName($uri_parts[0]);
//	}
	
	static protected function identifyName($string, $identifier){
		return (strpos($string, $identifier) !== false) ? true : false; 
	}
	
//	static protected function identifyQuery($string, $query_key){
//		return ($query_key == $string); 
//	}
	
	static protected function getName($id, $id_field, $name_field, $table, $identifier, $cache_folder, $languages_id, $languages_code, $languages_field = "languages_id"){
		$id = (int)$id;
		$cache_filename = self::buildFileName($id, $languages_code);
		if(($name = SSUCache::read($cache_filename, $cache_folder)) !== false)
			return $name;
		
		$sql_query = "SELECT $name_field FROM $table WHERE $id_field ='$id'";
		if($languages_id != 0)$sql_query .= " AND $languages_field = '$languages_id'";
		
		$_name = self::getNameFromDB($sql_query, $name_field);
		// fall back to default language
		if(empty($_name) && $languages_id != 0){
			$sql_query = "SELECT $name_field FROM $table WHERE $id_field ='$id' AND $languages_field = 1";
			$_name = self::getNameFromDB($sql_query, $name_field);
		}
		
		if(empty($_name))
			$_name = $name_field;
		
		$_name = SSULanguage::parseName($_name, $languages_code);
		$name = $_name.$identifier.$id;
		
		SSUCache::write($cache_filename, $cache_folder, $name);
		
		// write to link alias
		if(SSUConfig::registry('configs', 'auto_alias')){
			// here we want to make sure we use the product id without the attribute, we dont want to create aliases for attributes
			if(!is_numeric($id) && ($sem_pos = strpos($id, ':')) !== false){
				$id = substr($id, 0, $sem_pos);
				$name = $_name.$identifier.$id;
			}
			SSUAlias::autoAlias($id, $cache_folder, $name, $_name);
		}
		return $name;
	}
	
	static protected function getNameFromDB($sql_query, $name_field){
		global $db;
		$result = '';
		$sql_result = $db->Execute($sql_query);
		if($sql_result->RecordCount() > 0){
			if(!empty($name_field))
				$result = $sql_result->fields[$name_field];
			else 
				$result = $sql_result;
		}
		return $result;
	}
	
	static protected function buildFileName($id, $languages_code){
		return self::getID($id, '_').'_'.$languages_code;
	}
	
	/* 
	* Gets int id from a string (product/category name)
	*/
	static protected function getID($string, $delimiter){
		return end(explode($delimiter, $string));
	}
	
}