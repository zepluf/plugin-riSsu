<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: categories.php 268 2009-11-08 05:37:36Z yellow1912 $
*/

class ZMRiSsuParserCategory extends ZMRiSsuParser{
    protected $table            = TABLE_CATEGORIES_DESCRIPTION;
    protected $name_field       = "categories_name";
    protected $identifier       = "categories";
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
        // if we have cache then we can grab it and move out
        $cache_filename = $this->buildFileName($cPath, $languages_code);
        if(($name = ZMRiSsuCache::instance()->read($cache_filename, $this->table)) !== false)
            return $name;

        // rebuild cPath just to make sure
        $cPath = $this->rebuildCpath($cPath);
        $current_categories_id = $this->getID($cPath, '_');
        $category_ids = explode('_', $cPath);
        $cat_count = count($category_ids);

        $counter = $cat_count - (int)$this->plugin->get('maxCategoryLevel');

        if($counter < 0) $counter = 0;

        $_name = array();
        // this may not be the best way to build the category name, but we do this once per cPath only
        while($counter<=($cat_count-1)){
            $category_ids[$counter] = (int)$category_ids[$counter];
            $sql_query = "SELECT categories_name FROM ".$this->table." WHERE categories_id ='".$category_ids[$counter]."' AND language_id= '$languages_id' LIMIT 1";
            $__name = $this->getNameFromDB($sql_query, $this->name_field);
            // fall back to default language
            if(empty($__name) && $languages_id != 0){
                $sql_query = "SELECT categories_name FROM ".$this->table." WHERE categories_id ='".$category_ids[$counter]."' AND language_id = 1 LIMIT 1";
                $__name = $this->getNameFromDB($sql_query, $this->name_field);
            }

            $_name[] = ZMRiSsuLanguage::instance()->parseName($__name, $languages_code);
            $counter++;
        }

        if(empty($_name)) $_name = ZMRiSsuLanguage::instance()->parseName($name_field, $languages_code);

        $name = implode($this->plugin->get('nameDelimiter'), $_name).$identifier.$cPath;

        // write to file EVEN if we get an empty content
        ZMRiSsuCache::instance()->write($cache_filename, $this->table, $name);

        // write to link alias
        if(ZMLangUtils::asBoolean($this->plugin->get('aliasStatus')) && ZMLangUtils::asBoolean($this->plugin->get('autoAliasStatus'))){
            foreach($_name as $k => $v)
                $_name[$k] = $v.$this->plugin->get('idDelimiter').$category_ids[$k];

            $_name = implode($this->plugin->get('categorySeparator'), $_name);

            ZMRiSsuAlias::instance()->autoAlias($current_categories_id, $this->name_field, $name, $_name);
            return $_name;
        }

        return $name;

    }
}
