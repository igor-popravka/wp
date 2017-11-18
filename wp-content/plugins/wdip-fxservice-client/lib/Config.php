<?php
namespace WDIP\Plugin;

/**
 * @property array OPTIONS_PAGE
 * @property array PLUGIN_SETTINGS
 * @property array FXSERVICE_API
 */
class Config extends ObjectData {
    private static $instance;

    public static function instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function parse($file) {
        $data = (new \IniParser())->parse(Services::system()->getFullPath($file));
        $this->fromObjectArray($data);
    }
}