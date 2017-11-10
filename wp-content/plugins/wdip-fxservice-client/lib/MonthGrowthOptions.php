<?php
namespace WDIP\Plugin;

/**
 * @property $series
 * @property $categories
 */
class MonthGrowthOptions extends MyFXBookOptions {
    protected function generate() {
        $raw_data = [];
        $basic = 0;
        foreach ($this->accountid as $id) {
            $raw_data = array_merge($raw_data, $this->getModel()->getGrowthData($id, $basic));
            $basic = $raw_data[count($raw_data) - 1][1];
        }

        $group_data = [];
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
        }, $group_data);

        $categories = array_map(function ($val) {
            return $val[0];
        }, $group_data);

        $this->categories = array_values($categories);

        $series_data = array_map(function ($val) {
            return $val[1];
        }, $group_data);

        $this->series = [[
            'name' => 'Quest',
            'data' => array_values($series_data),
            'color' => 'rgba(124, 181, 236, 0.7)',
            'negativeColor' => 'rgba(255, 79, 79, 0.7)'
        ]];
    }
}