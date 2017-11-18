<?php
namespace WDIP\Plugin\Options;

/**
 * @property $series
 * @property $adminUrl
 */
class CalculatorForm extends AbstractOptions {
    protected function generate(array $data) {
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