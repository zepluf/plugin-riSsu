<?php
namespace plugins\riSsu;

use plugins\riCore\PluginCore;
use plugins\riPlugin\Plugin;

class RiSsu extends PluginCore{

    public function init(){
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
            $autoLoadConfig[80][] = array('autoType'=>'include', 'loadFile'=> __DIR__ . '/lib/decode.php');
        }
    }

    public function install(){
        // create the list of spiders
        $spiders = file(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'spiders.txt');
        foreach($spiders as $key => $spider){
            $spiders[$key] = trim($spider);
        }
        Plugin::saveSettings('riSsu', array('spiders' => $spiders));

        return Plugin::get('riCore.DatabasePatch')->executeSqlFile(file(__DIR__ . '/install/sql/install.sql'));
    }
    
    public function uninstall(){
        return Plugin::get('riCore.DatabasePatch')->executeSqlFile(file(__DIR__ . '/install/sql/uninstall.sql'));
    }
}