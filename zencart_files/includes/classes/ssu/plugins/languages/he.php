<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: he.php.sample 149 2009-03-04 05:23:35Z yellow1912 $
*/
  // note: we can later move part of this function into sub-functions, which we can store in the base class.
class SSULanguageHe extends SSULanguage{      
    static function parseName($name){
        $hebrew = array("חצאיות", "מכנסיים", "אביזרים", "שמלות", "וו", "יי", "א", "ב", "ג", "ד", "ה", "ו", "ז", "ח", "ט", "י", "ך", "כ", "ל", "ם", "מ", "ן", "נ", "ס", "ע", "ף", "פ", "ץ", "צ", "ק", "ר", "ש", "ת");
 		
				$translit = array("khatzayot", "mehnasaim", "avizarim", "smalot", "v", "ai", "a", "b", "g", "d", "h", "o", "z", "kh", "t", "i", "kh", "k", "l", "m", "m", "n", "n", "s", "a", "f", "p", "tz", "tz", "k", "r", "sh", "t");
        
        $name = str_replace($hebrew, $translit, $name);
        
        $name = strtolower($name);
   
        // we replace any non alpha numeric characters by the name delimiter
        $name = self::removeNonAlphaNumeric($name, SSUConfig::registry('delimiters', 'name'));
        
        // Remove short words first
        $name = self::removeShortWords($name, SSUConfig::registry('configs', 'minimum_word_length'), SSUConfig::registry('delimiters', 'name'));
        
        // trim the sentence
        $name = self::trimLongName($name);
                
        // remove excess SSUConfig::registry('delimiters', 'name')
        $name = self::removeDelimiter($name);
        
        // remove identifiers
        $name = self::removeIdentifiers($name);
        
        // remove trailing _
        $name = trim($name, SSUConfig::registry('delimiters', 'name'));
        
        return urlencode($name);
    }
} 