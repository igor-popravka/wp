<?php
/**
 * Created by PhpStorm.
 * User: igor.popravka
 * Date: 01.10.2017
 * Time: 8:40
 */

namespace WDIP\Plugin;


class FXServiceData implements \Iterator, \ArrayAccess {
    private $data = [];

    public function offsetExists($offset) {
        return $this->__isset($offset);
    }

    public function offsetGet($offset) {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value) {
        $this->__set($offset, $value);
    }

    public function offsetUnset($offset) {
        $this->__unset($offset);
    }

    public function current() {
        return current($this->data);
    }

    public function next() {
        next($this->data);
    }

    public function key() {
        return key($this->data);
    }

    public function valid() {
        return $this->offsetExists($this->key());
    }

    public function rewind() {
        reset($this->data);
    }

    public function __construct($data = []) {
        $this->__init($data);
    }

    public function has($name) {
        return $this->__isset($name);
    }

    public function toJSON() {
        return json_encode($this->data);
    }

    public function __isset($name) {
        return isset($this->data[$name]);
    }

    public function __set($name, $value) {
        $this->data[$name] = is_numeric($value) ? floatval($value) : $value;
    }

    public function __get($name) {
        return $this->__isset($name) ? $this->data[$name] : null;
    }

    public function __unset($name) {
        unset($this->data[$name]);
    }

    private function __init($data) {
        foreach ($data as $name => $value) {
            $this[$name] = $value;
        }
    }
}