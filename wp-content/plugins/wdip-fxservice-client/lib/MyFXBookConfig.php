<?php
/**
 * Created by PhpStorm.
 * User: igor.popravka
 * Date: 01.10.2017
 * Time: 1:19
 */

namespace WDIP\Plugin;


class MyFXBookConfig {
    /** @var \ArrayObject */
    private $config;
    
    private static $instance;

    private function __construct() {
        $config = WDIP_ROOT . '/wdip-config.ini';
        $this->config = (new \IniParser($config))->parse();
    }

    public static function instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    function __get($name) {
        return $this->config[$name];
    }

    function __set($name, $value) {
        $this->config[$name] = $value;
    }

    function __isset($name) {
        return $this->config->offsetExists($name);
    }

    function __unset($name) {
        unset($this->config[$name]);
    }
}