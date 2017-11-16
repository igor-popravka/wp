<?php
namespace WDIP\Plugin\Attributes;


abstract class AbstractCollectionAttributes implements \Iterator {
    private $collection = [];

    abstract protected function getAttrConfig ();

    protected function fromArray(array $attributes) {
        $config = $this->getAttrConfig();
        
        foreach ($attributes as $name => $value) {
            $attr = new Attribute($name, isset($config[$name]) ? $config[$name] : []);
            
            if($attr->isEmpty()){
                $attr->setValue($attr->getDefault());
            }
            
            $this->add($attr);
        }
    }
    
    public function toArray(){
        $attributes = [];
        foreach ($this->collection as $attr){
            /** @var Attribute $attr */
            $attributes = array_merge($attributes, $attr->toArray());
        }
        return $attributes;
    }

    public function add(Attribute $attr) {
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