<?php
namespace WDIP\Plugin\Options;

use WDIP\Plugin\Plugin;
use WDIP\Plugin\Services;

/**
 * @property $series
 * @property $categories
 * @property $serviceClient
 */
class MonthGrowth extends AbstractOptions {
    protected function generate(array $data) {
        $group_data = [];
        foreach ($data as $item) {
            $date = \DateTime::createFromFormat("m/d/Y", $item[0]);

            $group_name = $date->format('Ym');
            if (!isset($group_data[$group_name])) {
                $group_data[$group_name] = [];
            }

            $day = intval($date->format('d'));
            $group_data[$group_name][$day] = [$date->format("M 'y"), $item[1]];
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

    protected function getData() {
        switch ($this->serviceClient){
            case Plugin::SHORT_CODE_MYFXBOOK:
                $basic = 0;
                $result = [];
                foreach ($this->accountId as $id) {
                    $result = array_merge($result, Services::model()->getGrowthData($id, $basic));
                    $basic = $result[count($result) - 1][1];
                }
                return $result;
            case Plugin::SHORT_CODE_FXBLUE:
                return Services::model()->getFXBlueChartData($this->chartType);
        }
        return [];
    }
}