<?php
/**
 * Created by PhpStorm.
 * User: igor.popravka
 * Date: 14.11.2017
 * Time: 16:27
 */

namespace WDIP\Plugin\Attributes;


abstract class CollectionAttributes implements \Iterator {
    private $collection = [];

    abstract protected function getConfigurationMap();

    public function add(Attribute $attr) {
        $map = $this->getConfigurationMap();

        if (isset($map[$attr->getName()])) {
            $value = $attr->getValue();
            if (!isset($value) && isset($map[$attr->getName()]['default-value'])) {

                

            }
        }

        //if(empty($attr->getValue()) && )

        $this->collection[$attr->getName()] = $attr;
    }

    public function get($name) {
        return $this->has($name) ? $this->collection[$name] : null;
    }

    public function has($name) {
        return isset($this->collection[$name]);
    }

    public function __toString() {
        $result = [];
        foreach ($this->collection as $attr) {
            $result[] = strval($attr);
        }
        return implode(' ', $result);
    }

    public function current() {
        return current($this->collection);
    }

    public function next() {
        next($this->collection);
    }

    public function key() {
        return key($this->collection);
    }

    public function valid() {
        return $this->has($this->key());
    }

    public function rewind() {
        reset($this->collection);
    }
}