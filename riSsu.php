<?php
namespace plugins\riSsu;

use plugins\riCore\PluginCore;
use plugins\riPlugin\Plugin;

class riSsu extends PluginCore{
    
    public function install(){
        return $this->container->get('riCore.DatabasePatch')->executeSqlFile(file(__DIR__ . '/sql/install.sql'));
        return true;
    }
    
    public function uninstall(){
        return $this->container->get('riCore.DatabasePatch')->executeSqlFile(file(__DIR__ . '/sql/uninstall.sql'));
        return true;
    }
}