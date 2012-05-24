<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: config.php 272 2009-11-09 17:34:36Z yellow1912 $
*/
// do some hardcode for now
use zenmagick\base\Runtime;
$ssuConfig = array(
    'cores' => array('plugin', 'link', 'language', 'alias', 'cache' , 'parser'),
    // We define plugins in array so that we can just disable any plugin anytime we want
    'plugins' =>array(// we can have more than 1 plugin type here
        'parsers' => array('category', 'product', 'page')
    ),
    'pages' => array(
        'category' => array('parser' => 'ZMRiSsuParserCategory', 'identifier' => 'c', 'extension' => '', 'alias' => ''),
        'product_info' => array('parser' => 'ZMRiSsuParserProduct', 'identifier' => 'p'),
        'product_music_info' => array('parser' => 'ZMRiSsuParserProduct', 'identifier' => 'm'),
        'document_general_info' =>  array('parser' => 'ZMRiSsuParserProduct', 'identifier' => 'g'),
        'document_product_info' => array('parser' => 'ZMRiSsuParserProduct', 'identifier' => 'd'),
        'product_free_shipping_info' => array('parser' => 'ZMRiSsuParserProduct', 'identifier' => 'f'),
        'document_website_info' => array('parser' => 'ZMRiSsuParserProduct', 'identifier' => 'w'),
        'page' => array('parser' => 'ZMRiSsuParserPage', 'identifier' => 'Page'),
        'manufacturer' => array('parser' => 'ZMRiSsuParserManufacturer', 'identifier' => 'manufacturer', 'extension' => '', 'alias' => 'manufacturers'),
        'news_articles' => array('parser' => 'ZMRiSsuParserNewsArticle', 'identifier' => 'article')
    ),
    'paths' => array(
        'cores' => ZM_BASE_PATH . 'plugins/riSsu/cores/',
        'cache' => dirname(Runtime::getInstallationPath()).'/cache/ssu/', // @todo use ZM cache dir instead?
    ),
    'languages' => array(
        'default' => 'default',
        'en' => 'default'
    )
);
