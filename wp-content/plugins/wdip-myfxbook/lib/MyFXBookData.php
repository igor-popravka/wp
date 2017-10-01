<?php
/**
 * Created by PhpStorm.
 * User: igor.popravka
 * Date: 01.10.2017
 * Time: 8:40
 */

namespace WDIP\Plugin;


class MyFXBookData extends \ArrayObject {
    public function __construct($data = []) {
        if (!empty($data)){
            $this->__init($data);
        }
    }

    public function has($name) {
        return $this->__isset($name);
    }
    
    public function toJSON() {
        return json_encode($this);
    }

    public function __isset($name) {
        return isset($this[$name]);
    }

    public function __set($name, $value) {
        $this[$name] = $value;
    }

    public function __get($name) {
        return $this->__isset($name) ? $this[$name] : '';
    }

    public function __unset($name) {
        unset($this[$name]);
    }

    private function __init($data){
        foreach ($data as $name => $value) {
            $this[$name] = is_numeric($value) ? floatval($value) : $value;
        }
    }
}