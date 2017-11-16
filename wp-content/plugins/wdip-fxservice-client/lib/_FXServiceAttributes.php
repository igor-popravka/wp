<?php
/**
 * Created by PhpStorm.
 * User: igor.popravka
 * Date: 01.10.2017
 * Time: 0:08
 */

namespace WDIP\Plugin;

/**
 * @property $charttype
 * @property $accountid
 * @property $backgroundcolor
 * @property $gridlinecolor
 * @property $title
 * @property $fee
 */

class FXServiceAttributes extends FXServiceData {
    public function __construct(array $data) {
        if(isset($data['accountid'])){
            $data['accountid'] = explode(',', $data['accountid']); 
        }

        if (isset($data['fee'])) {
            $data['fee'] = explode(',', $data['fee']);
        }

        parent::__construct($data);
    }
}