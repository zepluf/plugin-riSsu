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
class LanguageDefault extends Language{
	public function parseName($name){

	    // strip tags
	    $name = strip_tags($name);
	    
		if(!empty($this->cyrillic))
		$name = mb_str_replace($this->cyrillic, $this->translit, $name);
		
		$name = mb_strtolower($name);

		// we replace any non alpha numeric characters by the name delimiter
		$name = $this->removeNonAlphaNumeric($name, Plugin::get('riPlugin.Settings')->get('riSsu.delimiters.name'));
			
		// Remove short words first
		$name = $this->removeShortWords($name, Plugin::get('riPlugin.Settings')->get('riSsu.minimum_word_length'), Plugin::get('riPlugin.Settings')->get('riSsu.delimiters.name'));
			
		// trim the sentence
		$name = $this->trimLongName($name);
			
		// remove excess Plugin::get('riPlugin.Settings')->get('riSsu.delimiters', 'name')
		$name = $this->removeDelimiter($name);
			
		// remove identifiers
		$name = $this->removeIdentifiers($name);
			
		// remove trailing _
		$name = trim($name, Plugin::get('riPlugin.Settings')->get('riSsu.delimiters.name'));
			
		return urlencode($name);
	}
}