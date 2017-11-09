<?php
namespace WDIP\Plugin;

/**
 * @property $series
 * @property $categories
 */
class FXBlueColumnChartOptions extends MyFXBookOptions {
    protected function generate() {
        $raw_data = $this->getModel()->getFXBlueChartData($this->charttype);

        /*$group_data = [];
        foreach ($raw_data as $data) {
            $date = \DateTime::createFromFormat("m/d/Y", $data[0]);

            $group_name = $date->format('Ym');
            if (!isset($group_data[$group_name])) {
                $group_data[$group_name] = [];
            }

            $day = intval($date->format('d'));
            $group_data[$group_name][$day] = [$date->format("M 'y"), $data[1]];
        }

        $group_data = array_map(function ($val) {
            $lastDay = max(array_keys($val));
            return $val[$lastDay];
        }, $group_data);*/

        $categories = array_map(function ($val) {
            $date = \DateTime::createFromFormat("m/d/Y", $val[0]);
            return $date->format("M 'y");
        }, $raw_data);

        $this->categories = array_values($categories);

        $series_data = array_map(function ($val) {
            return $val[1];
        }, $raw_data);

        $this->series = [[
            'name' => 'Quest',
            'data' => array_values($series_data),
            'color' => 'rgba(124, 181, 236, 0.7)',
            'negativeColor' => 'rgba(255, 79, 79, 0.7)'
        ]];
    }
}