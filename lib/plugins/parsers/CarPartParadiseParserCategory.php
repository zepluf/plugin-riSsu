<?php

//use plugins\riSSu\plugins\parsers\ParserCategory;
/**
 * A special parser for carpartparadise.com to remove top category level
 * @package Pages
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: categories.php 268 2009-11-08 05:37:36Z yellow1912 $

 */


namespace plugins\riSsu\plugins\parsers;

use plugins\riPlugin\Plugin;


class CarPartParadiseParserCategory extends ParserCategory{

    public function getName($cPath, $identifier, $languages_id, $languages_code){

        if(empty($identifier)) $identifier = $this->identifiers['categories'];

        // if we have cache then we can grab it and move out
        $cache_filename = $this->buildFileName($cPath, $languages_code);
        if(($name = Plugin::get('riCache.Cache')->read('ssu/' . $this->table . '/' . $cache_filename)) !== false)
            return $name;

        // rebuild cPath just to make sure
        $cPath = $this->rebuildCpath($cPath);
        $current_categories_id = $this->getID($cPath, '_');
        $category_ids = explode('_', $cPath);
        $cat_count = count($category_ids);

        if($cat_count > 1) {
            unset($category_ids[0]);
            $category_ids = array_values($category_ids);
            $cat_count--;
        }

        $maximum_level = (int)Plugin::get('settings')->get('riSsu.category_maximum_level');

        if($maximum_level < 0) $maximum_level = 0;
        $_name = array();

        // this may not be the best way to build the category name, but we do this once per cPath only
        while($maximum_level > 0 && $cat_count > 0){
            $maximum_level--;
            $cat_count--;
            $category_ids[$cat_count] = (int)$category_ids[$cat_count];
            $sql_query = "SELECT categories_name FROM ".$this->table." WHERE categories_id ='".$category_ids[$cat_count]."' AND language_id= '$languages_id' LIMIT 1";
            $__name = $this->getNameFromDB($sql_query, $this->name_field);
            // fall back to default language
            if(empty($__name) && $languages_id != 0){
                $sql_query = "SELECT categories_name FROM ".$this->table." WHERE categories_id ='".$category_ids[$cat_count]."' AND language_id = 1 LIMIT 1";
                $__name = $this->getNameFromDB($sql_query, $this->name_field);
            }

            array_unshift($_name, Plugin::get('riSsu.Language')->parseName($__name, $languages_code));
        }

        if(empty($_name)) $_name = Plugin::get('riSsu.Language')->parseName($this->name_field, $languages_code);

        $name = implode(Plugin::get('settings')->get('riSsu.delimiters.name'), $_name).$identifier.$cPath;

        $_name = implode(Plugin::get('settings')->get('riSsu.category_separator'), $_name);

        //$this->processName($name);
        $this->processName($_name);

        // write to file EVEN if we get an empty content
        Plugin::get('riCache.Cache')->write('ssu/' . $this->table . '/' . $cache_filename, $name);

        // write to link alias
        if(Plugin::get('settings')->get('riSsu.alias_status') && Plugin::get('settings')->get('riSsu.auto_alias')){
            if(Plugin::get('riSsu.Alias')->autoAlias($current_categories_id, $identifier, $this->name_field, $name, $_name)){
                //return $_name;
            }
        }

        return $name;
    }
}