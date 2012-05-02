<?php

namespace plugins\riSsu\plugins\parsers;

use plugins\riPlugin\Plugin;
use plugins\riSsu\cores\Parser;

/**
 * @package Pages
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: products.php 271 2009-11-09 04:27:13Z yellow1912 $
 */

class ParserProduct extends Parser{
    protected $table            = TABLE_PRODUCTS_DESCRIPTION;
    protected $name_field       = "products_name";
    protected $id_field         = "products_id";
    protected $main_page        = "product_info";
    protected $identifiers       = array('product_info'	=>	'p',
										'product_music_info'			=>	'm',
										'document_general_info'			=>	'g',
										'document_product_info'			=>	'd',
										'product_free_shipping_info'	=>	'f',
										'document_website_info'			=>	'w');
    protected $query_key        = "products_id";

    public function getDynamicQueryKeys($parameters){
        if(empty($parameters)) return $parameters;
        unset($parameters[$this->query_key]);
        //unset($parameters['cPath']);
        return $parameters;
    }

    public function getStaticQueryKeys($parameters, $identifier, $languages_id, $languages_code){
        $result = array();
        
        if(Plugin::get('riPlugin.Settings')->get('riSsu.category_in_product')){
            $result[] = Plugin::get('riSsu.ParserCategory')->getName($this->getProductPath($parameters[$this->query_key], $parameters['cPath']), null, $languages_id, $languages_code);            
        }        
        unset($parameters['cPath']);
                
        $result[] = $this->getName($parameters[$this->query_key], $identifier, $languages_id, $languages_code);
        
        return $result;
    }
    
    public function getProductPath($products_id, $cPath = null) {
        global $db;
        if(!empty($cPath)){
            $categories_id = $this->getID($cPath, '_');
            $category_query = "select p2c.categories_id, p.master_categories_id
                               from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                               where p.products_id = :products_id
                               and p.products_id = p2c.products_id
                               and (p.master_categories_id = :master_categories_id or p2c.categories_id= :categories_id) limit 1";
            	
            $category_query = $db->bindVars($category_query, ':products_id', $products_id, 'integer');
            $category_query = $db->bindVars($category_query, ':categories_id', $categories_id, 'integer');
            $category_query = $db->bindVars($category_query, ':master_categories_id', $categories_id, 'integer');
             
            $category = $db->Execute($category_query);
        }
        // fall back if needed to
        if (empty($cPath) || null != $category){
            $category_query = "select p.master_categories_id
                                from " . TABLE_PRODUCTS . " p
                                where p.products_id = :products_id limit 1";

            $category_query = $db->bindVars($category_query, ':products_id', $products_id, 'integer');

            $category = $db->Execute($category_query);

            if ($category->RecordCount() > 0) $categories_id = $category->fields['master_categories_id'];
        }

        $cPath = "";
        $categories = array();
        Plugin::get('riSsu.ParserCategory')->getParentCategoriesIds($categories, $categories_id);

        $categories = array_reverse($categories);

        $cPath = implode('_', $categories);

        if ('' != trim($cPath)) $cPath .= '_';
        $cPath .= $categories_id;

        return $cPath;
    }
    
}
