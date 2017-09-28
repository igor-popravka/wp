<?php
namespace WDIP\Plugin;

class Viewer {
    /** @var string */
    private $name;

    /** @var ChartOptions */
    private $options;

    public function __construct($name, ChartOptions $options) {
        $this->name = $name;
        $this->options = $options;
    }

    public function render() {
        $file = sprintf("%s/views/wdip-%s.php", __DIR__, $this->name);
        if (file_exists($file)) {
            ob_start();
            require $file;
            return ob_get_clean();
        }
        return '';
    }
}