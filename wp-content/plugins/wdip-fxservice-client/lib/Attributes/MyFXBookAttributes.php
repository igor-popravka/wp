<?php
/**
 * Created by PhpStorm.
 * User: igor.popravka
 * Date: 14.11.2017
 * Time: 16:49
 */

namespace WDIP\Plugin\Attributes;


class MyFXBookAttributes extends CollectionAttributes {
    protected function getConfigurationMap() {
        return [
            'account-id' => ['required' => false, 'default-value' => '']
        ];
    }

}