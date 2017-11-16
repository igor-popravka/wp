<?php
namespace WDIP\Plugin;

/**
 * @author: igor.popravka
 * Date: 24.10.2017
 * Time: 11:42
 */
class Cache {
    private static $instance;

    private static $cache = [];

    private function __construct() {
    }

    public static function instance(){
        if(!isset(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getValue($key, $default = null){
        if($this->isSetKey($key)){
            return self::$cache[$key];
        }
        return $default;
    }

    public function setValue($key, $value){
        self::$cache[$key] = $value;
    }

    public function isSetKey($key){
        return isset(self::$cache[$key]);
    }
}