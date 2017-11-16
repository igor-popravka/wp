<?php
namespace WDIP\Plugin;

class Viewer {
    /** @var ObjectData */
    private $options;

    private static $instance;
    
    private function __construct() {
    }

    public function render($name, ObjectData $options = null) {
        $this->options = $options;
        $file = sprintf("%s/views/wdip-%s.php", WDIP_ROOT, $name);
        if (file_exists($file)) {
            ob_start();
            require $file;
            return ob_get_clean();
        }
        return '';
    }
    
    public function output($name, ObjectData $options = null) {
        echo $this->render($name, $options);
    }

    public function getOptions(){
        return $this->options;
    }
    
    public static function instance (){
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }
}