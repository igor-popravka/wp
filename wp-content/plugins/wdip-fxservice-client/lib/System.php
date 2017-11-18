<?php
/**
 * Created by PhpStorm.
 * User: igor.popravka
 * Date: 18.11.2017
 * Time: 11:16
 */

namespace WDIP\Plugin;


class System {
    private $plugin_full_path = '';

    private static $instance;

    public static function instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function registerPlugin($full_path){
        $this->plugin_full_path = $full_path;
    }

    public function getPluginFile(){
        return $this->plugin_full_path;
    }

    public function getPluginDir(){
        return pathinfo($this->plugin_full_path, PATHINFO_DIRNAME);
    }

    public function getFullPath($file){
        if(file_exists("{$this->getPluginDir()}/$file")){
            return "{$this->getPluginDir()}/$file";
        }

        if($path = stream_resolve_include_path($file)){
            return $path;
        }

        return '';
    }
    
    public function getCssURL($file){
        return plugins_url("/media/css/{$file}.css", $this->getPluginFile());
    }

    public function getJsURL($file){
        return plugins_url("/media/js/{$file}.js", $this->getPluginFile());
    }
    
    public function getViewPath($file){
        return $this->getFullPath("view/{$file}.php");
    }
}