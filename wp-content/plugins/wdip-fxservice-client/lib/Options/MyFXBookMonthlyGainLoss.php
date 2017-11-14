<?php
namespace WDIP\Plugin\Options;

/**
 * @property $series
 * @property $categories
 */
class MyFXBookMonthlyGainLoss extends AbstractOptions {
    protected function generate(array $data) {
        $group_data = [];
        foreach ($data as $dt) {
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

    protected function getData() {
        $result = [];
        foreach ($this->accountid as $id) {
            $result = array_merge($result, $this->getModel()->getGainLossData($id));
        }
        return $result;
    }
}