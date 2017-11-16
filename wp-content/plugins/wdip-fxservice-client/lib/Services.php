<?php
namespace WDIP\Plugin;


class Services {
    public static function config(){
        return Config::instance();
    }

    public static function cache(){
        return Cache::instance();
    }
    
    public static function viewer(){
        return Viewer::instance();
    }
    
    public static function request(){
        return new Request();
    }
    
    public static function model(){
        return Model::instance();
    }

    public static function plugin(){
        return Plugin::instance();
    }

    public static function http(){
        return HTTP::instance();
    }
}