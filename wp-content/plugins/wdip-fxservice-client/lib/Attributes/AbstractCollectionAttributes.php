<?php
namespace WDIP\Plugin\Attributes;


abstract class AbstractCollectionAttributes implements \Iterator {
    private $collection = [];

    abstract protected function getAttrConfig();

    public function __construct(array $attributes) {
        $this->fromConfig();
        $this->fromArray($attributes);
    }

    private function fromConfig() {
        $attributes = $this->getAttrConfig();
        foreach ($attributes as $name => $config) {
            $attr = new Attribute($name, null, $config);
            $this->add($attr);
        }
    }

    protected function fromArray(array $attributes) {
        foreach ($attributes as $name => $value) {
            if ($this->has($name)) {
                $attr = $this->get($name);
                $attr->setValue($value);

                if ($attr->isEmpty()) {
                    $attr->setValue($attr->getDefault());
                }
            } else {
                $attr = new Attribute($name, $value);
            }

            $this->add($attr);
        }
    }

    public function isValid() {
        foreach ($this->collection as $attr) {
            /** @var Attribute $attr */
            if (!$attr->isValid()) {
                return false;
            }
        }
        return true;
    }

    public function toArray() {
        $attributes = [];
        foreach ($this->collection as $attr) {
            /** @var Attribute $attr */
            $attributes = array_merge($attributes, $attr->toArray());
        }
        return $attributes;
    }

    public function add(Attribute $attr) {
        $this->collection[$attr->getName()] = $attr;
    }

    /**
     * @param $name
     * @return Attribute
     */
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