<?php
namespace plugins\riSsu;

use plugins\riCore\PluginCore;
use plugins\riPlugin\Plugin;

class riSsu extends PluginCore{
    
    public function install(){
        if(file_exists($sql_file = __DIR__ . '/../sql/install.sql')){
            return Plugin::get('riCore.DatabasePatch')->executeSqlFile($sql_file);            
        }
        return true;
    }
    
    public function uninstall(){
        if(file_exists($sql_file = __DIR__ . '/../sql/uninstall.sql')){
            return Plugin::get('riCore.DatabasePatch')->executeSqlFile($sql_file);            
        }
        return true;
    }
}