<?php

namespace plugins\riSsu\plugins\languages;

use plugins\riPlugin\Plugin;

use plugins\riSsu\cores\Language;

/**
 * @package Pages
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: default.php 149 2009-03-04 05:23:35Z yellow1912 $
 */
// note: we can later move part of this function into sub-functions, which we can store in the base class.
class LanguageCustom extends LanguageDefault{
	public function parse($name){
	    // remove the stupid dots
	    $name = str_replace(array(".", "'", "`"), '', $name);
	    
	    // find the % off and remove them 
	    $pat = '/[0-9]+%/';
		$matches = preg_split($pat, $name);
		$name = $matches[0];
	    
		return parent::parse($name);		
	}
}