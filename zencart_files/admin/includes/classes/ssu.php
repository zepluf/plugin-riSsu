<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: ssu.php 309 2010-02-13 16:47:52Z yellow1912 $
*/
require_once(DIR_FS_CATALOG.'includes/init_includes/init_ssu.php');
class SSUManager {
	
	static protected $error_counter;
	static protected $file_counter = 0;
	// Part of the code in the below function taken from http://www.php.net/unlink
	// ggarciaa at gmail dot com (04-July-2007 01:57)
	// I needed to empty a directory, but keeping it
	// so I slightly modified the contribution from
	// stefano at takys dot it (28-Dec-2005 11:57)
	// A short but powerfull recursive function
	// that works also if the dirs contain hidden files
	//
	// $dir = the target directory
	// $DeleteMe = if true delete also $dir, if false leave it alone
	// sureRemoveDir('EmptyMe', false);
	// sureRemoveDir('RemoveMe', true);
	
	// TODO: chmod if needed
	function sureRemoveDir($dir, $DeleteMe) {
		global $messageStack;
	    if(!$dh = @opendir($dir)){
	    	$messageStack->add("Could not open dir $dir", 'warning');
	    	return;
	    }
	    if(self::$file_counter > SSU_MAX_CACHE_DELETE){
	    	return;
	    }
	    while (false !== ($obj = readdir($dh))) {
	        if($obj=='.' || $obj=='..') continue;
	        if (!@unlink($dir.'/'.$obj)) self::sureRemoveDir($dir.'/'.$obj, $DeleteMe);
	        else self::$file_counter++;
	        if(self::$file_counter >= SSU_MAX_CACHE_DELETE){
						return;
	        }
	    }
	
	    closedir($dh);
	    if ($DeleteMe){
	        @rmdir($dir);
	    }
	}

	function resetCache($cache_folder){
		global $messageStack;
		self::$file_counter = 0;
		
		if($cache_folder == 'all'){		
			$cache_folder = SSUConfig::registry('paths', 'cache');
			if(!@is_writable($cache_folder))
				$messageStack->add("$cache_folder folder is not writable", 'error');
			else	
				self::sureRemoveDir($cache_folder, false);
		}
		else{
			$cache_folder = SSUConfig::registry('paths', 'cache').$cache_folder;
			if(!@is_writable($cache_folder))
				$messageStack->add("$cache_folder folder is not writable", 'error');
			else	
				self::sureRemoveDir($cache_folder, false);
			
			$cache_folder = SSUConfig::registry('paths', 'cache').'pc';	
			if(!@is_writable($cache_folder))
				$messageStack->add("$cache_folder folder is not writable", 'error');
			else	
				self::sureRemoveDir($cache_folder, false);
		}	
		self::resetCacheTimer();
		return self::$file_counter;
	}
	
	function retrieveAliases(){
		global $db;
		$aliases = $db->Execute('SELECT * FROM '.TABLE_LINKS_ALIASES);	
		$result = array();
		while(!$aliases->EOF){
			$temp_array = array();
			foreach($aliases->fields as $key => $value)
				$temp_array[$key] = $value;
			$result[] = $temp_array;
			$aliases->MoveNext();
		}
		return $result;				
	}
	
	function autoBuildAliases($parser){
		echo call_user_func_array(array("{$parser}Parser", "getStatic"), array('table'));
		
	}
	
	function checkAndFixCache(){
		$cache_folder = SSUConfig::registry('paths', 'cache');
		self::sureRemoveDir($cache_folder, true);
		return self::$file_counter;
	}
	
	static function removeCache($id_list){
			global $db;
			if(!is_array($id_list)) $id_list = array($id_list);
			foreach($id_list as $id){
				// use the id to delete alias cache
				$alias_cache = $db->Execute("SELECT * FROM ".TABLE_SSU_CACHE." WHERE referring_id = $id and type = 'aliases' LIMIT 1");
				// delete alias cache
				$cache_folder = SSUConfig::registry('paths', 'cache')."aliases/".chunk_split(substr($alias_cache->fields['file'] , 0, 4), 1, '/');
				$cache_folder = rtrim($cache_folder, '/').'/';
				if(@unlink($cache_folder.$alias_cache->fields['file'])){
					// now we have to delete all things related
					$db->Execute("DELETE FROM ".TABLE_SSU_CACHE." WHERE file='{$alias_cache->fields['file']}' AND type='aliases'");
				}
				
				// get the related cache files
				$pc = $db->Execute("SELECT * FROM ".TABLE_LINKS_ALIASES." WHERE id = $id");
				$pc_cache = $db->Execute("SELECT * FROM ".TABLE_SSU_CACHE." WHERE referring_id = {$pc->fields['referring_id']} and type = '{$pc->fields['alias_type']}'");
				while (!$pc_cache->EOF) {
					$cache_folder = SSUConfig::registry('paths', 'cache')."pc/".chunk_split(substr($pc_cache->fields['file'] , 0, 4), 1, '/');
					$cache_folder = rtrim($cache_folder, '/').'/';
					if(@unlink($cache_folder.$pc_cache->fields['file'])){
						// now we have to delete all things related
						$db->Execute("DELETE FROM ".TABLE_SSU_CACHE." WHERE file='{$pc_cache->fields['file']}' AND type!='aliases'");
					}
					$pc_cache->MoveNext();
				}
			}
		}
		
		static function resetCacheTimer(){
			global $db;
			$db->Execute('UPDATE '.TABLE_CONFIGURATION.' SET configuration_value = '. time(). ' WHERE configuration_key="SSU_CACHE_RESET_TIME"');			
		}
}