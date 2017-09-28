<?php

function wdip_autoload($class) {
    $class = str_replace("WDIP\\Plugin\\", "", $class);
    $file = sprintf('%s/class.wdip-%s.php', __DIR__, strtolower($class));
    if(file_exists($file)){
      require_once ($file);
    }
}

spl_autoload_register('wdip_autoload');