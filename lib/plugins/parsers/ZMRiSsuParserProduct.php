<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: products.php 271 2009-11-09 04:27:13Z yellow1912 $
*/
use zenmagick\base\Runtime;
class ZMRiSsuParserProduct extends ZMRiSsuParser{
    protected $table            = TABLE_PRODUCTS_DESCRIPTION;
    protected $name_field       = "products_name";
    protected $id_field         = "products_id";
    protected $main_page        = "product_info";
    protected $identifier       = "products";
    protected $query_key        = "products_id";
    /**
     * Get instance.
     */
    public static function instance() {
        return Runtime::getContainer()->get('ZMRiSsuParserProduct');
    }

    public function getDynamicQueryKeys($parameters){
        if(empty($parameters)) return $parameters;
        unset($parameters[$this->query_key]);
        unset($parameters['cPath']);
        return $parameters;
    }

    public function processParameters(&$parameters, $identifier, $languages_id, $languages_code){
        $result = array();
        if(ZMLangUtils::asBoolean($this->plugin->get('categoryInProductStatus'))){
            if(isset($parameters['cPath'])){
                $result[] = ZMRiSsuParserCategory::instance()->getName($this->getProductPath($parameters[$this->query_key], $parameters['cPath']), '-c-', $languages_id, $languages_code);
            }
            else {
                $result[] = ZMRiSsuParserCategory::instance()->getName($parameters[$this->query_key], '-c-', $languages_id, $languages_code);
            }
        }
        unset($parameters['cPath']);
        $result[] = $this->getName($parameters[$this->query_key], $identifier, $languages_id, $languages_code);
        unset($parameters[$this->query_key]);
        return $result;
    }

    public function getProductPath($products_id, $cPath = null) {
        $conn = ZMRuntime::getDatabase();
        if(!empty($cPath)){
            $categories_id = $this->getID($cPath, '_');
            $category_query = "select p2c.categories_id, p.master_categories_id
                               from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                               where p.products_id = :products_id
                               and p.products_id = p2c.products_id
                               and (p.master_categories_id = :master_categories_id  or p2c.categories_id= :categories_id) limit 1";

            $args = array('products_id' => $products_id, 'master_categories_id' => $categories_id,  'categories_id' => $categories_id);
            $category = $conn->querySingle($category_query, $args, array(TABLE_PRODUCTS, TABLE_PRODUCTS_TO_CATEGORIES));
        }
        // fall back if needed to
        if (empty($cPath) || null != $category){
            $category_query = "select p.master_categories_id
                                from " . TABLE_PRODUCTS . " p
                                where p.products_id = :products_id limit 1";
            $args = array('products_id' => $products_id);
            $category = $conn->querySingle($category_query, $args, array(TABLE_PRODUCTS));

            if (!empty($category)) $categories_id = $category['master_categories_id'];
        }

        $cPath = "";
        $categories = array();
        $this->getParentCategoriesIds($categories, $categories_id);

        $categories = array_reverse($categories);

        $cPath = implode('_', $categories);

        if ('' != trim($cPath)) $cPath .= '_';
        $cPath .= $categories_id;

        return $cPath;
    }
}
