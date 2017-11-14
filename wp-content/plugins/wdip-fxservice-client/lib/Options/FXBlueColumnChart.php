<?php
namespace WDIP\Plugin\Options;

/**
 * @property $series
 * @property $categories
 */
class FXBlueColumnChart extends AbstractOptions {
    protected function generate(array $data) {
        $categories = array_map(function ($val) {
            $date = \DateTime::createFromFormat("m/d/Y", $val[0]);
            return $date->format("M 'y");
        }, $data);

        $this->categories = array_values($categories);

        $series_data = array_map(function ($val) {
            return $val[1];
        }, $data);

        $this->series = [[
            'name' => 'Quest',
            'data' => array_values($series_data),
            'color' => 'rgba(124, 181, 236, 0.7)',
            'negativeColor' => 'rgba(255, 79, 79, 0.7)'
        ]];
    }

    protected function getData() {
        return $this->getModel()->getFXBlueChartData($this->charttype);
    }


}