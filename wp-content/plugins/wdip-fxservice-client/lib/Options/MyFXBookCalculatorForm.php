<?php
namespace WDIP\Plugin\Options;

use WDIP\Plugin\FXServiceData;
use WDIP\Plugin\MyFXBookConfig;
/**
 * @property $series
 * @property FXServiceData $CalcFormOptions
 * @property $adminUrl
 */
class MyFXBookCalculatorForm extends AbstractOptions {
    protected function generate(array $data) {
        $this->CalcFormOptions = new FXServiceData(MyFXBookConfig::instance()->CALCULATOR_FORM);
        $this->adminUrl = admin_url('admin-ajax.php');
        $this->series = $data;
    }

    protected function getData() {
        return [
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