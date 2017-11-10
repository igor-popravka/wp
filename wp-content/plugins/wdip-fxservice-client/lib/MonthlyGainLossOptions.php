<?php
/**
 * Created by PhpStorm.
 * User: igor.popravka
 * Date: 31.10.2017
 * Time: 23:44
 */

namespace WDIP\Plugin;

/**
 * @property $series
 * @property $categories
 */
class MonthlyGainLossOptions extends MyFXBookOptions{
    protected function generate() {
        $raw_data = [];
        foreach ($this->accountid as $id) {
            $raw_data = array_merge($raw_data, $this->getModel()->getGainLossData($id));
        }

        $group_data = [];
        foreach ($raw_data as $dt) {
            $combine_data = array_combine($dt[0], $dt[1]);
            foreach ($combine_data as $date => $val) {
                if (isset($group_data[$date])) {
                    $group_data[$date][1] += $val;
                } else {
                    $group_data[$date] = [
                        \DateTime::createFromFormat('M Y', $date)->format("M 'y"),
                        $val
                    ];
                }
            }
        };

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