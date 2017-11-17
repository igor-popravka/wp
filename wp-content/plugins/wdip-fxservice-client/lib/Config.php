<?php
namespace WDIP\Plugin;

/**
 * @property array SHORT_CODES
 * @property array OPTIONS_PAGE
 * @property array PLUGIN_SETTINGS
*/
class Config extends ObjectData{
    private static $instance;

    public static function instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }
    
    public function parse($file){
        $data = (new \IniParser())->parse($file);
        $this->fromObjectArray($data);
    }
}