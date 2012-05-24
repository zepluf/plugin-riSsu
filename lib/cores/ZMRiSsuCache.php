<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: cache.php 274 2009-11-20 17:13:45Z yellow1912 $
*/
use zenmagick\base\ZMObject;
class ZMRiSsuCache extends ZMObject {
    private $cache;

    public static function instance() {
        return zenmagick\base\Runtime::getContainer()->get('ZMRiSsuCache');
    }

    public function write($name, $cache_folder, $content, $use_subfolder = false){//return false;
        $this->cache[$cache_folder][$name] = $content;

        $cache_folder = SSUConfig::registry('paths', 'cache')."$cache_folder/";
        if($use_subfolder){
            $path = substr($name , 0, 4);
            $cache_folder .= chunk_split($path, 1, '/');
        }

        $cache_folder = rtrim($cache_folder, '/');
        if(!is_dir($cache_folder)){
            $old_umask = umask(0);
            @mkdir($cache_folder, 0777, true);
            umask($old_umask);
        }
        $write = @file_put_contents("$cache_folder/$name", $content);
        @chmod("$cache_folder/$name", 0777);
        return $write;
    }

    public function read($name, $cache_folder, $use_subfolder = false){//return false;
        if(isset($this->cache[$cache_folder][$name]))
            return $this->cache[$cache_folder][$name];

        $cache_folder = SSUConfig::registry('paths', 'cache')."$cache_folder/";
        if($use_subfolder){
            $path = substr($name , 0, 4);
            $cache_folder .= chunk_split($path, 1, '/');
        }
        $cache_folder = rtrim($cache_folder, '/').'/';

        $read = @file_get_contents("$cache_folder$name");
        return $read;
    }

    public function saveCachePath($id_list, $type, $file){
        foreach ($id_list as $id) {
            if (!empty($id) && is_numeric($id)) {
                $sql = 'INSERT IGNORE INTO '.TABLE_SSU_CACHE."(referring_id, type, file) VALUES(:referring_id, :type, :file)";
                $args = array('referring_id' => $id, 'type' => $type, 'file' => $file);
                ZMRuntime::getDatabase()->update($sql, $args, TABLE_SSU_CACHE);
            }

        }
    }

    public function exists($name, $cache_folder, $use_subfolder){
        $cache_folder = SSUConfig::registry('paths', 'cache')."$cache_folder/";
        if($use_subfolder){
            $path = substr($name , 0, 4);
            $cache_folder .= chunk_split($path, 1, '/');
        }

        $cache_folder = rtrim($cache_folder, '/');
        return file_exists("$cache_folder/$name");
    }
}
