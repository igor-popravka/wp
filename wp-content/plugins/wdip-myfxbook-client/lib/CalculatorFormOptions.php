<?php
/**
 * Created by PhpStorm.
 * User: igor.popravka
 * Date: 01.11.2017
 * Time: 1:04
 */

namespace WDIP\Plugin;

/**
 * @property $series
 * @property MyFXBookData $CalcFormOptions
 * @property $adminUrl
 */
class CalculatorFormOptions extends MyFXBookOptions {
    protected function generate() {
        $this->CalcFormOptions = new MyFXBookData(MyFXBookConfig::instance()->CALCULATOR_FORM);
        $this->adminUrl = admin_url('admin-ajax.php');
        $this->series = [
            [
                "name" => "Total",
                "data" => [],
                "color" => "#2D8AC7"
            ],
            [
                "name" => "Gain",
                "data" => [],
                "color" => "#7CA821"
            ],
            [
                "name" => "Fee",
                "data" => [],
                "color" => "#A94442"

            ]
        ];
    }
}