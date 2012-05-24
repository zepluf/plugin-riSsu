<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: es.php 218 2009-07-20 02:38:17Z yellow1912 $
*/
    // note: we can later move part of this function into sub-functions, which we can store in the base class.
class SSULanguageTr extends SSULanguage{
    private $from = array("þ", "Þ", "&#351;", "&#350;", "ç", "Ç", "&#231;", "&#199;", "Ü", "ü", "&#220;", "&#252;", "ö", "Ö", "&#246;", "&#214;", "ð", "Ð", "&#287;", "&#286;", "ý", "Ý", "&#305;", "&#304;");
    private $to = array("s", "S", "s", "S", "c", "C", "c", "C", "U", "u", "U", "u", "o", "O", "o", "O", "g", "G", "g", "G", "i", "I", "i", "I");
}