<?php
namespace WDIP\Plugin;

class Viewer {
    /** @var ObjectData */
    private $options;

    private static $instance;

    private function __construct() {
    }

    public function render($file, ObjectData $options = null) {
        $this->options = $options;
        $path = Services::system()->getViewPath($file);
        if (file_exists($path)) {
            ob_start();
            require $path;
            return ob_get_clean();
        }
        return '';
    }

    public function output($file, ObjectData $options = null) {
        echo $this->render($file, $options);
    }

    public function getOptions() {
        return $this->options;
    }

    public static function instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }
}