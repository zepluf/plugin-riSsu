<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: ssu_definitions.php 249 2009-09-10 04:27:39Z yellow1912 $
*/

define('SSU_ALIAS_DEFAULT_TYPE', 1);
define('SSU_ALIAS_CATEGORY_TYPE', 2);
define('SSU_ALIAS_PRODUCT_TYPE', 3);
define('SSU_ALIAS_PAGE_TYPE', 4);
define('SSU_ALIAS_MANUFACTURER_TYPE', 5);
define('SSU_MAX_CACHE_DELETE', 1000);
// filenames
define('FILENAME_SSU', 'ssu.php');
// database
define('TABLE_LINKS_ALIASES', DB_PREFIX.'links_aliases');
define('TABLE_SSU_CACHE', DB_PREFIX.'ssu_cache');