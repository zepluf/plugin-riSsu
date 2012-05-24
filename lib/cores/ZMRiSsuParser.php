<?php
/**
* @package
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: parser.php 339 2010-08-09 02:24:51Z yellow1912 $
*/
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;
class ZMRiSsuParser extends ZMObject{
    public $plugin;
    protected $languages_field = 'language_id';
    public function __construct(){
        $this->plugin = Runtime::getContainer()->get('plugins')->getPluginForId('riSsu');
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return Runtime::getContainer()->get(get_called_class());
    }

    public function processPage(&$page, $alias = null){
        $page = $alias;
    }

    public function getCacheKey($page, $parameters){
        return $page.'_'.$parameters[$this->query_key];
    }

    public function getDynamicQueryKeys($parameters){
        if(empty($parameters)) return $parameters;
        unset($parameters[$this->query_key]);
        return $parameters;
    }

    public function getStaticQueryKeys($parameters, $identifier, $languages_id, $languages_code){
        if(!isset($parameters[$this->query_key]) || empty($parameters[$this->query_key])) return '';
        return array($this->getName($parameters[$this->query_key], $identifier, $languages_id, $languages_code));
    }

    public function reverseProcessParameter($parameter){
        return array($this->query_key => $this->getID($parameter, $this->plugin->get('idDelimiter')));
    }

    public function getName($id, $identifier, $languages_id, $languages_code){
        $id = (int)$id;
        $cache_filename = $this->buildFileName($id, $languages_code);
        if(($name = ZMRiSsuCache::instance()->read($cache_filename, $this->table)) !== false)
            return $name;

        $sql_query = 'SELECT '.$this->name_field.' FROM '.$this->table.' WHERE '.$this->id_field.' = '.$id;
        if($languages_id != 0 && !empty($this->languages_field)) $sql_query .= ' AND '.$this->languages_field.' = '.$languages_id;

        $_name = $this->getNameFromDB($sql_query, $this->name_field);
        // fall back to default language
        if(empty($_name) && $languages_id != 0 && !empty($this->languages_field)){
            $sql_query = 'SELECT '.$this->name_field.' FROM '.$this->table.' WHERE '.$this->id_field.' = '.$id.' AND '.$this->languages_field.' = 1';
            $_name = $this->getNameFromDB($sql_query, $this->name_field);
        }

        if(empty($_name)) $_name = $name_field;
        $_name = ZMRiSsuLanguage::instance()->parseName($_name, $languages_code);

        $name = $_name.$identifier.$id;

        // write to file EVEN if we get an empty content
        ZMRiSsuCache::instance()->write($cache_filename, $this->table, $name);

        // write to link alias
        if(ZMLangUtils::asBoolean($this->plugin->get('aliasStatus')) && ZMLangUtils::asBoolean($this->plugin->get('autoAliasStatus'))){
            $_name .= $this->plugin->get('idDelimiter').$id;
            ZMRiSsuAlias::instance()->autoAlias($id, $this->name_field, $name, $_name);
            return $_name;
        }

        return $name;
    }

    public function getNameFromDB($sql_query, $name_field) {
        $result = '';
        $sql_result = ZMRuntime::getDatabase()->querySingle($sql_query, array());
        if (!empty($sql_result)) {
            if (!empty($name_field)) {
                $result = $sql_result[$name_field];
            } else {
                $result = $sql_result;
            }
        }
        return $result;
    }

    public function buildFileName($id, $languages_code){
        return $this->getID($id, '_').'_'.$languages_code;
    }

    /*
    * Gets int id from a string (product/category name)
    */
    public function getID($string, $delimiter){
		$a = explode($delimiter, $string);
        return end($a);
    }


    public function updateGet($string, &$_get){
        $_get[$this->query_key] = $this->getID($string, $this->plugin->get('idDelimiter'));
    }
	
	public function getParentCategoriesIds(&$categories, $categories_id){
		$conn = ZMRuntime::getDatabase();
		$parent_categories_query = "select parent_id
									from " . TABLE_CATEGORIES . "
									where categories_id = ':categories_id'";

		$parent_categories = $conn->query($parent_categories_query, array('categories_id' => $categories_id), TABLE_CATEGORIES);

		foreach ($parent_categories as $parent_category) {
		  if ($parent_category['parent_id'] == 0) return true;
		  $categories[sizeof($categories)] = $parent_category['parent_id'];
		  if ($parent_category['parent_id'] != $categories_id) {
			$this->getParentCategoriesIds($categories, $parent_category['parent_id']);
		  }		  
		}
	}
}
