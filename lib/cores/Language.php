<?php

namespace plugins\riSsu\cores;

use plugins\riPlugin\Plugin;

/**
 * @package Pages
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: language.php 204 2009-05-10 05:00:57Z yellow1912 $
 */
class Language{

	protected $cyrillic = array(),
	$translit = array();
	
	public function parseName($name, $languages_code){
		$languages_class = $this->loadLanguageParser($languages_code);
		return Plugin::get('riSsu.' . $languages_class)->parse($name);
	}
	
    public function parse($name){

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

	public function loadLanguageParser($languages_code){
		$languages = Plugin::get('riPlugin.Settings')->get('riSsu.languages');
		if(array_key_exists($languages_code, $languages))
		$languages_parser = $languages[$languages_code];
		else
		$languages_parser = 'default';
		$languages_class = 'Language'.ucfirst($languages_parser);
		//if(!class_exists($languages_class))
		//SSUPlugin::load('languages', $languages_parser);
		return $languages_class;
	}

	public function removeDelimiter($name){
		// remove excess $this->registry('name_delimiter')
		// $name = preg_replace('/'.Plugin::get('riPlugin.Settings')->get('riSsu.delimiters', 'name').Plugin::get('riPlugin.Settings')->get('riSsu.delimiters', 'name').'+/', Plugin::get('riPlugin.Settings')->get('riSsu.delimiters', 'name'), $name);
		$name_delimiter = Plugin::get('riPlugin.Settings')->get('riSsu.delimiters.name');
		while(strpos($name, $name_delimiter.$name_delimiter) !== false)
		$name = str_replace($name_delimiter.$name_delimiter, $name_delimiter, $name);

		// remove anything that looks like our identifiers in the name
			
		//foreach(Plugin::get('riPlugin.Settings')->get('riSsu.pages') as $options)
		//	$name = str_replace($options['identifier'], '', $name);

		return $name;
	}

	public function removeShortWords($string, $minimum_word_length, $name_delimiter){
		if($minimum_word_length > 0){
			$name_parts = explode($name_delimiter, $string);
			foreach($name_parts as $key => $value)
			if(mb_strlen($value) < $minimum_word_length) unset($name_parts[$key]);
			$string = implode($name_delimiter, $name_parts);
		}
		return $string;
	}

	function trimLongName($name){
		if (Plugin::get('riPlugin.Settings')->get('riSsu.max_name_length') > 0 && (mb_strlen($name) > Plugin::get('riPlugin.Settings')->get('riSsu.max_name_length'))){
			preg_match('/(.{' . Plugin::get('riPlugin.Settings')->get('riSsu.max_name_length') . '}.*?)\b/', $name, $matches);
			$name = rtrim($matches[1]);
		}
		return $name;
	}

	public function removeSpecialChars($string, $name_delimiter){
		return str_replace(array(' ', '\'', '/', '\\', '"', '.', ':', '@', '_', '-', '?', '&', '='), $name_delimiter, $string);
	}

	function removeNonAlphaNumeric($name, $name_delimiter){
		return preg_replace("/[^a-zA-Z0-9]/", $name_delimiter, $name);
	}

	public function utf16Urlencode ( $str ) {
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
	 
	public function removeIdentifiers($name){
		$name = Plugin::get('riPlugin.Settings')->get('riSsu.delimiters.id').$name.Plugin::get('riPlugin.Settings')->get('riSsu.delimiters.id');
		
		foreach(Plugin::get('riPlugin.Settings')->get('riSsu.parsers') as $parser){			
			foreach (Plugin::get('riSsu.' . $parser)->getIdentifiers() as $page => $identifier)
				$name = str_replace($identifier, '', $name);				
		}
				
		return trim($name, Plugin::get('riPlugin.Settings')->get('riSsu.delimiters.id'));
	}
}