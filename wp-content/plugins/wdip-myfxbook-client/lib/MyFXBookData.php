<?php
/**
 * Created by PhpStorm.
 * User: igor.popravka
 * Date: 01.10.2017
 * Time: 8:40
 */

namespace WDIP\Plugin;


class MyFXBookData extends \ArrayObject {
    private static $count = 0;
    private $model;

    public function __construct($data = []) {
        $this->model = new MyFXBookModel();
        
        if (!empty($data)){
            $this->__init($data);
        }
        $this->generateUID();
    }

    /**
     * @return MyFXBookModel
     */
    public function getModel(){
        return $this->model;
    }

    private function generateUID() {
        $uid_string = sprintf("%s-%s-%d-%d", static::class, $this->charttype, time(), self::$count++);
        $this->uid = md5($uid_string);
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