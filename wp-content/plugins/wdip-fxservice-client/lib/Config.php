<?php
namespace WDIP\Plugin;

/**
 * @property int version
 * @property array SETTINGS
 * @property array FXSERVICE_API
 * @property array CALCULATOR_CHART_OPTIONS
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
        $this->fromArray($data);
    }
}