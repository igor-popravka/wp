<?php
namespace WDIP\Plugin;

/**
 * @property array SHORT_CODES
 * @property array PLUGIN_OPTIONS
*/
class Config {
    /** @var ObjectData */
    private $config;

    /** @var \IniParser */
    private $provider;
    
    private static $instance;

    private function __construct() {
        $this->provider = new \IniParser();
    }

    public static function instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }
    
    public function parse($file){
        $data = $this->provider->parse($file);
        $this->config = new ObjectData($data);
    }

    function __get($name) {
        return $this->config[$name];
    }

    function __set($name, $value) {
        $this->config[$name] = $value;
    }

    function __isset($name) {
        return $this->config->__isset($name);
    }

    function __unset($name) {
        unset($this->config[$name]);
    }
}