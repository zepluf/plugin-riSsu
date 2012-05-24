<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: es.php 285 2010-01-24 01:52:33Z yellow1912 $
*/
    // note: we can later move part of this function into sub-functions, which we can store in the base class.
class ZMRiSsuLanguageEs extends ZMRiSsuLanguage{
    private $from = array("ñ", "á", "é", "í", "ó", "&oacute;", "ú");
    private $to = array("n", "a", "e", "i", "o", "o", "u");
}