<?php
namespace WDIP\Plugin;

/**
 * @property $uid
 * @property $account_id
 * @property $type
 * @property $title
 * @property $height
 * @property $bgcolor
 * @property $width
 * @property $gridcolor
 * @property $filter
 * @property $series
 * @property $month_tick_interval
*/
class Options implements \ArrayAccess {
    private $options = [];

    private static $count = 0;

    public function __construct($options = []) {
        foreach ($options as $name => $value) {
            $this[$name] = $value;
        }
        $this->uid = $this->generateUID();
    }

    public function __isset($name) {
        return isset($this->options[$this->getName($name)]);
    }

    public function __set($name, $value) {
        $this->options[$this->getName($name)] = $value;
    }

    public function __get($name) {
        return $this->__isset($name) ? $this->options[$this->getName($name)] : '';
    }

    public function __unset($name) {
        unset($this->options[$this->getName($name)]);
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

    private function generateUID() {
        $hash_string = sprintf("%s-%s-%d-%d", __CLASS__, $this->type, time(), self::$count++);
        return md5($hash_string);
    }

    private function getName($name) {
        return str_replace('-', '_', $name);
    }

    public function toJSON() {
        $js = json_encode($this->options);
        return $js;
    }

    public function addSeries($data){
        if(!isset($this->series)){
            $this->series = [];
        }

        if(!empty($data)){
            $series = $this->series;
            $series[] = $data;
            $this->series = $series;
        }
    }
}