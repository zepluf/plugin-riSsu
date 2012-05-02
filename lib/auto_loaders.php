<?php
/**
 * @package Pages
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: config.ssu.php 319 2010-02-22 11:04:33Z yellow1912 $
 */
if(!IS_ADMIN_FLAG){
    global $autoLoadConfig;
    /*
     * Include SSU Config
     */
    // We need to modify the default load order of Zen. Language class must be loaded first
    $autoLoadConfig[80][] = array('autoType'=>'init_script', 'loadFile'=> 'init_languages.php');
    foreach ($autoLoadConfig[110] as $key => $value){
        if($value['loadFile'] == 'init_languages.php'){
            unset($autoLoadConfig[110][$key]);
            break;
        }
    }
    $autoLoadConfig[80][] = array('autoType'=>'include', 'loadFile'=> DIR_FS_CATALOG . 'plugins/riSsu/lib/decode.php');
}