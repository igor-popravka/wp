<?php
/**
 * Created by PhpStorm.
 * User: igor.popravka
 * Date: 01.10.2017
 * Time: 20:56
 */

namespace WDIP\Plugin;


class Request extends MyFXBookData {
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