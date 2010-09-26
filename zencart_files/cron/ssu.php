<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: ssu.php 286 2010-01-24 01:53:21Z yellow1912 $
*/

define('SSU_MAX_CACHE_DELETE', 9999999);

// please change admin folder name if necessary
require('../admin/includes/configure.php');
ini_set('include_path', DIR_FS_CATALOG . PATH_SEPARATOR . ini_get('include_path'));
chdir(DIR_FS_CATALOG); 

// now we can safely include application top
require("includes/application_top.php");

require(DIR_FS_ADMIN.DIR_WS_CLASSES.'ssu.php');
if(isset($_GET['folder'])) $folder = $_GET['folder'];
else $folder = 'all';

$files_removed = SSUManager::resetCache($folder);
//echo $files_removed;