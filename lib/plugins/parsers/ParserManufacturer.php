<?php

namespace plugins\riSsu\plugins\parsers;

use plugins\riSsu\Parser;

/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: manufacturers.php 272 2009-11-09 17:34:36Z yellow1912 $
*/
/**
 * This class will try to parse manufacturers_id= in the query string into a nice looking name
 * It also takes care of parsing back such strings
 *
 */
class ParserManufacturer extends Parser{
    protected $table            = TABLE_MANUFACTURERS;
    protected $name_field       = "manufacturers_name";
    protected $id_field         = "manufacturers_id";
    protected $query_key        = "manufacturers_id";
    protected $languages_field  = null;
}
