<?php

namespace plugins\riSsu\plugins\parsers;

use plugins\riSsu\cores\Parser;

/**
 * @package Pages
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: products.php 271 2009-11-09 04:27:13Z yellow1912 $
 */

class ParserStatic extends Parser{   
    public function getStaticQueryKeys($parameters, $identifier, $languages_id, $languages_code){
        return array();
    }
}
