<?php
namespace WDIP\Plugin;


class ObjectData implements \Iterator, \ArrayAccess {
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
        $this->fromObjectArray($data);
    }

    public function has($name) {
        return $this->__isset($name);
    }

    public function toJSON() {
        return json_encode($this->data);
    }

    public function __isset($name) {
        return isset($this->data[$this->normaliseName($name)]);
    }

    public function __set($name, $value) {
        $this->data[$this->normaliseName($name)] = is_numeric($value) ? floatval($value) : $value;
    }

    public function __get($name) {
        return $this->__isset($name) ? $this->data[$this->normaliseName($name)] : null;
    }

    public function __unset($name) {
        unset($this->data[$this->normaliseName($name)]);
    }

    public function fromObjectArray($data) {
        foreach ($data as $name => $value) {
            $this[$this->normaliseName($name)] = $value;
        }
    }
    
    private function normaliseName($name){
        $names = explode('-', $name);
        $name = strtolower(array_shift($names));
        foreach ($names as $part){
            $name .= ucfirst($part);
        }
        return $name;
    }
}