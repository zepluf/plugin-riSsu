<?php

namespace plugins\riSsu\cores;

use plugins\riPlugin\Plugin;

/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: parser.php 339 2010-08-09 02:24:51Z yellow1912 $
*/
class Parser{
	
    protected $languages_field = 'language_id';
	    
    protected $identifiers = array();
    
    public function addIdentifier($page, $identifier){
        $this->identifiers[$page] = Plugin::get('riPlugin.Settings')->get('riSsu.delimiters.id') . $identifier . Plugin::get('riPlugin.Settings')->get('riSsu.delimiters.id');       
    }
    
    /**
     * 
     * This function should identify the current page
     * @param array $parts
     * @param array $main_page
     */
    public function identifyPage(&$parts, &$_get){
        foreach($parts as $key => $part)    
            foreach ($this->identifiers as $page => $identifier){
                if(strpos($part, $identifier) !== false){
                    $_get['main_page'] = $page;
                    $_get = array_merge($_get, $this->reverseProcessParameter($part));
                    unset($parts[$key]);
                    return true;
                }
            }
        return false;
    }
    
    /**
     * 
     * Enter description here ...
     * @param string $part
     * @param array $_get
     */
    public function identifyParameter($part, &$_get){            
        foreach ($this->identifiers as $page => $identifier){
            if(strpos($part, $identifier) !== false){                    
                $_get = array_merge($_get, $this->reverseProcessParameter($part));                    
                return true;
            }
        }
        return false;
    }
    
    public function reverseProcessParameter($parameter){
        return array($this->query_key => $this->getID($parameter, Plugin::get('riPlugin.Settings')->get('riSsu.delimiters.id')));
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
        if(!isset($parameters[$this->query_key]) || empty($parameters[$this->query_key])) return array();
        return array($this->getName($parameters[$this->query_key], $identifier, $languages_id, $languages_code));
    }
    
    public function getName($id, $identifier, $languages_id, $languages_code){
        $id = (int)$id;
        $cache_filename = $this->buildFileName($id, $languages_code);
        if(($name = Plugin::get('riSsu.Cache')->read($cache_filename, $this->table)) !== false)
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
        $_name = Plugin::get('riSsu.Language')->parseName($_name, $languages_code);

        $name = $_name.$identifier.$id;

        // write to file EVEN if we get an empty content
        Plugin::get('riSsu.Cache')->write($cache_filename, $this->table, $name);

        // write to link alias
        if(Plugin::get('riPlugin.Settings')->get('riSsu.alias_status')){
            //$_name .= Plugin::get('riPlugin.Settings')->get('riSsu.delimiters.id').$id;
            Plugin::get('riSsu.Alias')->autoAlias($id, $this->name_field, $name, $_name);
            return $_name;
        }

        return $name;
    }
	
	protected function getNameFromDB($sql_query, $name_field){
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
	
	protected function buildFileName($id, $languages_code){
		return $this->getID($id, '_').'_'.$languages_code;
	}
	
	/* 
	* Gets int id from a string (product/category name)
	*/
	protected function getID($string, $delimiter){
		return end(explode($delimiter, $string));
	}
	
}