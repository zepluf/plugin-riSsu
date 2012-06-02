<?php

namespace plugins\riSsu\plugins\parsers;

use plugins\riPlugin\Plugin;
//use plugins\riSSu\plugins\parsers\ParserCategory;

/**
 * @package Pages
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: categories.php 268 2009-11-08 05:37:36Z yellow1912 $
 */

class ABCParserCategory extends ParserCategory{

    protected function processName(&$name){             
        // remove from after br
        if(($pos = strpos($name, Plugin::get('settings')->get('riSsu.delimiters.name') . 'br' . Plugin::get('settings')->get('riSsu.delimiters.name'))) !== false)
            $name = substr($name, 0, $pos);
            
        // remove from after off
        if(($pos = strpos($name, Plugin::get('settings')->get('riSsu.delimiters.name') . 'off' . Plugin::get('settings')->get('riSsu.delimiters.name'))) !== false)
            $name = substr($name, 0, $pos);
            
        // put in giftcard
        $name .= Plugin::get('settings')->get('riSsu.delimiters.name') . 'gift-cards';
    }

}