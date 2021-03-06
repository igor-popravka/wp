<?php
namespace WDIP\Plugin;


class Request extends ObjectData {
    public function __construct() {
        parent::__construct($_POST);
    }
    
    public function validate (array $post_fields){
        foreach ($post_fields as $field){
            if(!$this->has($field)){
                return false;
            }
        }
        return true;
    }
}