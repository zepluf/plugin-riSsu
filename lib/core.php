<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: core.php 149 2009-03-04 05:23:35Z yellow1912 $
*/
class SSUConfig{
    static $registry;

    static function init($configs){
        foreach($configs as $key => $value){
            if(is_array($value))
                self::registerArray($key, $value);
            else
                self::register($key, $value);
        }
    }

    static function register($class, $name, $value){
        self::$registry[$class][$name] = $value;
    }

    static function registry($class, $name=""){
        if(isset(self::$registry[$class])){
            if(!empty($name)){
                if(isset(self::$registry[$class][$name]))
                    return self::$registry[$class][$name];
            }
            else
                return self::$registry[$class];
        }
        return null;
    }

    static function registerArray($class, $params){
        foreach ($params as $key => $value)
            self::register($class, $key, $value);
    }

}
