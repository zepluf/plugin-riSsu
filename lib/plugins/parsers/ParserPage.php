<?php

namespace plugins\riSsu\plugins\parsers;

use plugins\riSsu\cores\Parser;

/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: pages.php 249 2009-09-10 04:27:39Z yellow1912 $
*/

class ParserPage extends Parser{
    protected $table        = TABLE_EZPAGES;
    protected $name_field   = "pages_title";
    protected $id_field     = "pages_id";
    protected $query_key    = "id";
    protected $languages_field = 'languages_id';
}
