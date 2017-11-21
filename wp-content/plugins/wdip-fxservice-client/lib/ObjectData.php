<?php

namespace WDIP\Plugin;


class ObjectData extends \ArrayObject {
    protected $data = [];

    public function __isset($name) {
        return array_key_exists($this->normaliseName($name), $this->data);
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

    public function __construct(array $data = []) {
        $this->fromArray($data);
    }

    public function has($name) {
        return $this->offsetExists($name);
    }

    public function toJSON() {
        return json_encode($this->data);
    }

    public function fromArray($data) {
        foreach ($data as $name => $value) {
            $this[$name] = $value;
        }
    }

    private function normaliseName($name) {
        if (strpos($name, '-') !== false) {
            $names = explode('-', $name);
            $name = strtolower(array_shift($names));
            foreach ($names as $part) {
                $name .= ucfirst($part);
            }
        }

        return $name;
    }

    public function getSource(){
        return $this->data;
    }
}