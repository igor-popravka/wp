<?php
/**
 * Created by PhpStorm.
 * User: igor.popravka
 * Date: 14.11.2017
 * Time: 16:23
 */

namespace WDIP\Plugin\Attributes;


class Attribute {
    private $name = '';
    private $value = null;
    private $required = false;

    public function __construct($name, $value, $required = false) {
        $this->name = $name;
        $this->value = is_numeric($value) ? floatval($value) : $value;
        $this->required = $required;
    }

    public function __toString() {
        return "{$this->name}={$this->value}";
    }

    public function isRequired() {
        return $this->required;
    }

    public function getName() {
        return $this->name;
    }

    public function &getValue() {
        return $this->value;
    }

    public function setValue($value) {
        $this->value = $value;
    }
}