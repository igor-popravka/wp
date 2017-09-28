<?php
namespace WDIP\Plugin;

class ChartOptions implements \ArrayAccess {
    public $id;
    public $type;
    public $title;
    public $height;
    public $width;
    public $bgcolor;
    public $gridcolor;
    public $filter;
    public $series;

    private static $count = 0;

    public function __construct($options = []) {
        foreach ($options as $name => $value) {
            $this[$name] = $value;
        }
        
        $this->id = $this->generateID();
    }

    public function offsetExists($offset) {
        return property_exists($this, $offset);
    }

    public function offsetGet($offset) {
        return $this->offsetExists($offset) ? $this->{$offset} : '';
    }

    public function offsetSet($offset, $value) {
        if ($this->offsetExists($offset)) {
            $this->{$offset} = $value;
        }
    }

    public function offsetUnset($offset) {
        if ($this->offsetExists($offset)) {
            unset($this->{$offset});
        }
    }

    private function generateID(){
        $hash_string = sprintf("%s-%s-%d-%d", __CLASS__, $this->type, time(), self::$count++);
        return md5($hash_string);
    }
    
    public function toJSON(){
        return json_encode($this);
    }
}