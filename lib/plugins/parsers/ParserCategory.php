<?php

namespace plugins\riSsu\plugins\parsers;

use plugins\riPlugin\Plugin;
use plugins\riSsu\cores\Parser;

/**
 * @package Pages
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: categories.php 268 2009-11-08 05:37:36Z yellow1912 $
 */

class ParserCategory extends Parser{
	protected $table            = TABLE_CATEGORIES_DESCRIPTION;
	protected $name_field       = "categories_name";
	protected $query_key        = "cPath";

	public function rebuildCpath($cPath){
		// do not trust the passed cPath, always rebuild it
		$current_categories_id = $this->getID($cPath, '_');
		$category_ids = array();
		$this->getParentCategoriesIds($category_ids, (int)$current_categories_id);
		$category_ids = array_reverse($category_ids);
		$category_ids[] = (int)$current_categories_id;
		return implode('_' , $category_ids);
	}

	public function getName($cPath, $identifier, $languages_id, $languages_code){
	    
	    if(empty($identifier)) $identifier = current($this->identifiers);
	    
		// if we have cache then we can grab it and move out
		$cache_filename = $this->buildFileName($cPath, $languages_code);
		if(($name = Plugin::get('riSsu.Cache')->read($cache_filename, $this->table)) !== false)
		return $name;

		// rebuild cPath just to make sure
		$cPath = $this->rebuildCpath($cPath);
		$current_categories_id = $this->getID($cPath, '_');
		$category_ids = explode('_', $cPath);
		$cat_count = count($category_ids);

		$counter = $cat_count - (int)Plugin::get('riPlugin.Settings')->get('riSsu.category_maximum_level');

		if($counter < 0) $counter = 0;

		$_name = array();
		// this may not be the best way to build the category name, but we do this once per cPath only
		while($counter <= ($cat_count-1)){
			$category_ids[$counter] = (int)$category_ids[$counter];
			$sql_query = "SELECT categories_name FROM ".$this->table." WHERE categories_id ='".$category_ids[$counter]."' AND language_id= '$languages_id' LIMIT 1";
			$__name = $this->getNameFromDB($sql_query, $this->name_field);
			// fall back to default language
			if(empty($__name) && $languages_id != 0){
				$sql_query = "SELECT categories_name FROM ".$this->table." WHERE categories_id ='".$category_ids[$counter]."' AND language_id = 1 LIMIT 1";
				$__name = $this->getNameFromDB($sql_query, $this->name_field);
			}

			$_name[] = Plugin::get('riSsu.Language')->parseName($__name, $languages_code);
			$counter++;
		}

		if(empty($_name)) $_name = Plugin::get('riSsu.Language')->parseName($name_field, $languages_code);

		$name = implode(Plugin::get('riPlugin.Settings')->get('riSsu.delimiters.name'), $_name).$identifier.$cPath;

		$_name = implode(Plugin::get('riPlugin.Settings')->get('riSsu.category_separator'), $_name);
		
        //$this->processName($name);
		$this->processName($_name);
		
		// write to file EVEN if we get an empty content
		Plugin::get('riSsu.Cache')->write($cache_filename, $this->table, $name);

		// write to link alias
		if(Plugin::get('riPlugin.Settings')->get('riSsu.alias_status') && Plugin::get('riPlugin.Settings')->get('riSsu.auto_alias')){
		    if(Plugin::get('riSsu.Alias')->autoAlias($current_categories_id, $this->name_field, $name, $_name))			
			    return $_name;
		}

		return $name;
	}
	
	protected function processName(&$name){
	    
	}
	
	public function getParentCategoriesIds(&$categories, $categories_id) {
	    global $db;
	    $parent_categories_query = "select parent_id
                                from " . TABLE_CATEGORIES . "
                                where categories_id = '" . (int)$categories_id . "'";

	    $parent_categories = $db->Execute($parent_categories_query);

	    while (!$parent_categories->EOF) {
	        if ($parent_categories->fields['parent_id'] == 0) return true;
	        $categories[sizeof($categories)] = $parent_categories->fields['parent_id'];
	        if ($parent_categories->fields['parent_id'] != $categories_id) {
	            zen_get_parent_categories($categories, $parent_categories->fields['parent_id']);
	        }
	        $parent_categories->MoveNext();
        }
    }
}
