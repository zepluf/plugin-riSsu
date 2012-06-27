<?php
namespace plugins\riSsu;

use plugins\riCore\PluginCore;
use plugins\riPlugin\Plugin;

class RiSsu extends PluginCore{
    
    public function install(){
        // create the list of spiders
        $spiders = file(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'spiders.txt');
        foreach($spiders as $key => $spider){
            $spiders[$key] = trim($spider);
        }
        Plugin::saveSettings('riSsu', array('spiders' => $spiders));

        return Plugin::get('riCore.DatabasePatch')->executeSqlFile(file(__DIR__ . '/install/sql/install.sql'));
        return true;
    }
    
    public function uninstall(){
        return Plugin::get('riCore.DatabasePatch')->executeSqlFile(file(__DIR__ . '/install/sql/uninstall.sql'));
        return true;
    }
}