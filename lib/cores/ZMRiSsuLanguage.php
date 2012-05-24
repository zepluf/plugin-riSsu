<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: language.php 204 2009-05-10 05:00:57Z yellow1912 $
*/
use zenmagick\base\ZMObject;
use zenmagick\base\Runtime;
class ZMRiSsuLanguage extends ZMObject{

    protected $from = array();
    protected $to = array();

    public function __construct(){
        $this->plugin = Runtime::getContainer()->get('plugins')->getPluginForId('riSsu');
    }

    public static function instance() {
        return Runtime::getContainer()->get(get_called_class());
    }

    public function parseName($name, $languages_code){
        $languages = SSUConfig::registry('languages');
        if(array_key_exists($languages_code, $languages))
            $languages_parser = $languages[$languages_code];
        else
            $languages_parser = 'default';
        $languages_class = 'ZMRiSsuLanguage'.ucfirst($languages_parser);

        return $languages_class::instance()->parse($name);
    }

    public function removeDelimiter($name){
        $name_delimiter = $this->plugin->get('nameDelimiter');
        while(strpos($name, $name_delimiter.$name_delimiter) !== false)
            $name = str_replace($name_delimiter.$name_delimiter, $name_delimiter, $name);
        return $name;
    }

    public function parse($name){
        if(!empty($this->from))
            $name = str_ireplace($this->form, $this->to, $name);
        $name = strtolower($name);

        // we replace any non alpha numeric characters by the name delimiter
        $name = $this->removeNonAlphaNumeric($name, $this->plugin->get('nameDelimiter'));

        // Remove short words first
        $name = $this->removeShortWords($name, (int)$this->plugin->get('minimumWordLength'), $this->plugin->get('nameDelimiter'));

        // trim the sentence
        $name = $this->trimLongName($name);

        // remove excess SSUConfig::registry('delimiters', 'name')
        $name = $this->removeDelimiter($name);

        // remove identifiers
        $name = $this->removeIdentifiers($name);

        // remove trailing
        $name = trim($name, $this->plugin->get('nameDelimiter'));

        return rawurlencode($name);
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

    private function trimLongName($name){
        if ((int)$this->plugin->get('maximumNameLength') > 0 && (mb_strlen($name) > (int)$this->plugin->get('maximumNameLength'))){
           preg_match('/(.{' . (int)$this->plugin->get('maximumNameLength') . '}.*?)\b/', $name, $matches);
           $name = rtrim($matches[1]);
       }
       return $name;
    }

    private function removeSpecialChars($string, $name_delimiter){
        return str_replace(array(' ', '\'', '/', '\\', '"', '.', ':', '@', '_', '-', '?', '&', '='), $name_delimiter, $string);
    }

    private function removeNonAlphaNumeric($name, $name_delimiter){
        return preg_replace("/[^a-zA-Z0-9]/", $name_delimiter, $name);
    }

    public function utf16Urlencode ( $str ) {
        # convert characters > 255 into HTML entities
        $convmap = array( 0xFF, 0x2FFFF, 0, 0xFFFF );
        $str = mb_encode_numericentity( $str, $convmap, "UTF-8");

        # escape HTML entities, so they are not urlencoded
        $str = preg_replace( '/&#([0-9a-fA-F]{2,5});/i', 'mark\\1mark', $str );
        $str = rawurlencode($str);

        # now convert escaped entities into unicode url syntax
        $str = preg_replace( '/mark([0-9a-fA-F]{2,5})mark/i', '%u\\1', $str );
        return $str;
    }

    public function removeIdentifiers($name){
        $name = $this->plugin->get('idDelimiter').$name.$this->plugin->get('idDelimiter');
        foreach(SSUConfig::registry('pages') as $page)
            $name = str_replace($page['identifier'], '', $name);

        return trim($name, $this->plugin->get('idDelimiter'));
    }
}
