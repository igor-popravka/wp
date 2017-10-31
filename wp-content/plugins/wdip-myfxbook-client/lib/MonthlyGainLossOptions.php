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
 * @property $monthtickinterval
 */
class MonthlyGainLossOptions extends MyFXBookOptions{
    protected function generate() {
        $this->monthtickinterval = 1000 * 3600 * 24 * 30;

        $data = [];
        foreach ($this->accountid as $id) {
            $data = array_merge($data, $this->getModel()->getGainLossData($id));
        }

        $series_data = [];
        foreach ($data as $dt) {
            $combine_data = array_combine($dt[0], $dt[1]);
            foreach ($combine_data as $date => $val) {
                if (isset($series_data[$date])) {
                    $series_data[$date][1] += $val;
                } else {
                    $series_data[$date] = [
                        ((\DateTime::createFromFormat('M Y', $date)->getTimestamp() * 1000) - $this->monthtickinterval),
                        $val
                    ];
                }
            }
        };

        $this->series = [[
            'name' => 'Quest',
            'data' => array_values($series_data),
            'color' => 'rgba(124, 181, 236, 0.7)',
            'negativeColor' => 'rgba(255, 79, 79, 0.7)'
        ]];
    }
}