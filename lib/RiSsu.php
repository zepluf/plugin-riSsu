<?php 

namespace plugins\riSsu;

class RiSsu{
	
	public function init(){
		global $autoLoadConfig;
		/*                
		 * Include SSU Config
		 */               
		$autoLoadConfig[80][] = array('autoType'=>'init_script', 'loadFile'=> 'init_ssu.php'); 
		
		$autoLoadConfig[80][] = array('autoType'=>'classInstantiate', 'className'=>'SSULink', 'objectName'=>'ssu', 'checkInstantiated'=>true, 'classSession'=>false);
		
		$autoLoadConfig[80][] = array('autoType'=>'objectMethod', 'objectName'=>'ssu', 'methodName' => 'parseUrl');
		
		// We need to modify the default load order of Zen. Language class must be loaded first
		$autoLoadConfig[80][] = array('autoType'=>'init_script', 'loadFile'=> 'init_languages.php');
		foreach ($autoLoadConfig[110] as $key => $value){
			if($value['loadFile'] == 'init_languages.php'){
				unset($autoLoadConfig[110][$key]);
				break;
			}
		}
		$autoLoadConfig[80][] = array('autoType'=>'init_script', 'loadFile'=> 'init_ssu_language.php'); 
		
		$autoLoadConfig[80][] = array('autoType'=>'objectMethod', 'objectName'=>'ssu', 'methodName' => 'postParseUrl');
	}
}