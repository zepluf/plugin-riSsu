<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: init_ssu_language.php 200 2009-04-24 00:03:51Z yellow1912 $
*/

$langConfig = array('languages_code'		=>	isset($_SESSION['languages_code']) ? $_SESSION['languages_code'] : DEFAULT_LANGUAGE,
					'languages_id'			=>	isset($_SESSION['languages_id']) ? (int)$_SESSION['languages_id'] : 1,
											);
											
SSUConfig::registerArray('configs', $langConfig);