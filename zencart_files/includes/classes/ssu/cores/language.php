<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: language.php 204 2009-05-10 05:00:57Z yellow1912 $
*/
	class SSULanguage{

		static function parseName($name, $languages_code){
			$languages_class = self::loadLanguageParser($languages_code);
			return call_user_func_array(array($languages_class, 'parseName'), array($name));
		}
		
		static function loadLanguageParser($languages_code){
			$languages = SSUConfig::registry('languages');
			if(array_key_exists($languages_code, $languages))
				$languages_parser = $languages[$languages_code];
			else 
				$languages_parser = 'default';
			$languages_class = 'SSULanguage'.ucfirst($languages_parser);
			if(!class_exists($languages_class))
				SSUPlugin::load('languages', $languages_parser);
			return $languages_class;
		}
		
		static function removeDelimiter($name){
			// remove excess self::registry('name_delimiter')
			// $name = preg_replace('/'.SSUConfig::registry('delimiters', 'name').SSUConfig::registry('delimiters', 'name').'+/', SSUConfig::registry('delimiters', 'name'), $name);
			$name_delimiter = SSUConfig::registry('delimiters', 'name');
			while(strpos($name, $name_delimiter.$name_delimiter) !== false)
				$name = str_replace($name_delimiter.$name_delimiter, $name_delimiter, $name);
				
			// remove anything that looks like our identifiers in the name
			
			foreach(SSUConfig::registry('identifiers') as $identifier)
				$name = str_replace($identifier, '', $name);
				
			return $name;
		}
		
		static function removeShortWords($string, $minimum_word_length, $name_delimiter){
			if($minimum_word_length > 0){
				$name_parts = explode($name_delimiter, $string);
				foreach($name_parts as $key => $value)
					if(mb_strlen($value) < $minimum_word_length) unset($name_parts[$key]);
				$string = implode($name_delimiter, $name_parts);
			}
			return $string;
		}
		
		function trimLongName($name){
			if (SSUConfig::registry('configs', 'max_name_length') > 0 && (mb_strlen($name) > SSUConfig::registry('configs', 'max_name_length'))){
		       preg_match('/(.{' . SSUConfig::registry('configs', 'max_name_length') . '}.*?)\b/', $name, $matches);
		       $name = rtrim($matches[1]);
		   }
		   return $name;
		}
		
		static function removeSpecialChars($string, $name_delimiter){
			return str_replace(array(' ', '\'', '/', '\\', '"', '.', ':', '@', '_', '-', '?', '&', '='), $name_delimiter, $string);
		}
		
		function removeNonAlphaNumeric($name, $name_delimiter){
			return preg_replace("/[^a-zA-Z0-9]/", $name_delimiter, $name);
		}

		static function utf16Urlencode ( $str ) {
	        # convert characters > 255 into HTML entities
	        $convmap = array( 0xFF, 0x2FFFF, 0, 0xFFFF );
	        $str = mb_encode_numericentity( $str, $convmap, "UTF-8");
	
	        # escape HTML entities, so they are not urlencoded
	        $str = preg_replace( '/&#([0-9a-fA-F]{2,5});/i', 'mark\\1mark', $str );
	        $str = urlencode($str);
	
	        # now convert escaped entities into unicode url syntax
	        $str = preg_replace( '/mark([0-9a-fA-F]{2,5})mark/i', '%u\\1', $str );
	        return $str;
    	}
    	
    	static function removeIdentifiers($name){
    		$name = SSUConfig::registry('delimiters', 'id').$name.SSUConfig::registry('delimiters', 'id');
    		foreach(SSUConfig::registry('identifiers') as $identifier)
				if(!is_array($identifier))
					$name = str_replace($identifier, '', $name);
				else 
					foreach ($identifier as $sub_dentifier)
						$name = str_replace($sub_dentifier, '', $name);
			return trim($name, SSUConfig::registry('delimiters', 'id'));
    	}
	}