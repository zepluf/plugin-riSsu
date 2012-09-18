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
        else{
            Plugin::get('riCjLoader.Loader')->addLibs(array(
                'jqGrid' => array(
                    '4.4.1' => array(
                        'css_files' => array(
                            'ui.jqgrid.css' => array(
                                'local' => 'riSsu::css/ui.jqgrid.css'
                            )
                        ),
                        'jscript_files' => array(
                            'grid.locale-en.js' => array(
                                'local' => 'riSsu::js/i18n/grid.locale-en.js',
                            ),
                            'grid.base.js' => array(
                                'local' => 'riSsu::js/grid.base.js',
                            ),
                            'grid.common.js' => array(
                                'local' => 'riSsu::js/grid.common.js',
                            ),
                            'grid.formedit.js' => array(
                                'local' => 'riSsu::js/grid.formedit.js',
                            ),
                            'grid.inlinedit.js' => array(
                                'local' => 'riSsu::js/grid.inlinedit.js',
                            ),
                            'grid.celledit.js' => array(
                                'local' => 'riSsu::js/grid.celledit.js',
                            ),
                            'grid.subgrid.js' => array(
                                'local' => 'riSsu::js/grid.subgrid.js',
                            ),
                            'grid.treegrid.js' => array(
                                'local' => 'riSsu::js/grid.treegrid.js',
                            ),
                            'grid.grouping.js' => array(
                                'local' => 'riSsu::js/grid.grouping.js',
                            ),
                            'grid.custom.js' => array(
                                'local' => 'riSsu::js/grid.custom.js',
                            ),
                            'grid.tbltogrid.js' => array(
                                'local' => 'riSsu::js/grid.tbltogrid.js',
                            ),
                            'grid.import.js' => array(
                                'local' => 'riSsu::js/grid.import.js',
                            ),
                            'jquery.fmatter.js' => array(
                                'local' => 'riSsu::js/jquery.fmatter.js',
                            ),
                            'JsonXml.js' => array(
                                'local' => 'riSsu::js/JsonXml.js',
                            ),
                            'grid.jqueryui.js' => array(
                                'local' => 'riSsu::js/grid.jqueryui.js',
                            ),
                            'grid.filter.js' => array(
                                'local' => 'riSsu::js/grid.filter.js',
                            )
                        )
                    )
                )
            ));
        }
    }

    public function install(){
        // create the list of spiders
        $spiders = file(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'spiders.txt');
        foreach($spiders as $key => $spider){
            $spiders[$key] = trim($spider);
        }
        Plugin::get('settings')->saveLocal('riSsu', array('spiders' => $spiders));

        return Plugin::get('riCore.DatabasePatch')->executeSqlFile(file(__DIR__ . '/install/sql/install.sql'));
    }

    public function uninstall(){
        return Plugin::get('riCore.DatabasePatch')->executeSqlFile(file(__DIR__ . '/install/sql/uninstall.sql'));
    }
}