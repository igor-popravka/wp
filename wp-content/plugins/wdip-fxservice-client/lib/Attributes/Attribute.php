<?php
namespace WDIP\Plugin\Attributes;


class Attribute {
    const TYPE_STRING = 1;
    const TYPE_NUMBER = 2;
    const TYPE_LIST = 3;

    private $name = '';
    private $value = null;
    private $required = false;
    private $default = null;
    private $type = self::TYPE_STRING;

    public function __construct($name, $value = null, array $config = []) {
        $this->name = $name;
        $this->value = $value;

        foreach ($config as $property => $value) {
            if (in_array($property, ['required', 'default', 'type'])) {
                $this->{$property} = $value;
            }
        }
    }

    public function isValid() {
        if ($this->isRequired()) {
            return !$this->isEmpty();
        }
        return true;
    }

    public function __toString() {
        return "{$this->name}={$this->value}";
    }

    public function isRequired() {
        return $this->required;
    }

    public function setRequired($required = false) {
        $this->required = $required;
        return $this;
    }

    public function getName() {
        return $this->name;
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue($value) {
        $this->value = $this->parseValue($value);
        return $this;
    }

    public function getDefault() {
        return $this->default;
    }

    public function setDefault($value) {
        $this->default = $this->parseValue($value);
        return $this;
    }

    public function isEmpty() {
        $value = $this->getValue();
        switch ($this->getType()) {
            case self::TYPE_NUMBER:
                return is_null($value);
            default:
                return empty($value);
        }
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type = self::TYPE_STRING) {
        $this->type = $type;
        return $this;
    }

    private function parseValue($value) {
        switch ($this->getType()) {
            case self::TYPE_NUMBER:
                return floatval($value);
            case self::TYPE_LIST:
                return explode(',', $value);
            default:
                return $value;
        }
    }

    public function toArray() {
        return [$this->getName() => $this->getValue()];
    }
}