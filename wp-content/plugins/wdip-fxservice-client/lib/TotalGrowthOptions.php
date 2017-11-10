<?php
/**
 * Created by PhpStorm.
 * User: igor.popravka
 * Date: 27.10.2017
 * Time: 0:11
 */

namespace WDIP\Plugin;

/**
 * @property $series
 * @property $monthtickinterval
 */
class TotalGrowthOptions extends MyFXBookOptions {
    protected function generate() {
        $this->monthtickinterval = 1000 * 3600 * 24 * 30;

        $series_data = [];
        $basic = 0;
        foreach ($this->accountid as $id) {
            $series_data = array_merge($series_data, $this->getModel()->getGrowthData($id, $basic));
            $basic = $series_data[count($series_data) - 1][1];
        }

        $series_data = array_map(function($val){
            return [
                \DateTime::createFromFormat("m/d/Y", $val[0])->getTimestamp() * 1000,
                $val[1]
            ];
        }, $series_data);

        $this->series =  [[
            'name' => 'Quest',
            'data' => $series_data,
            'color' => 'rgba(124, 181, 236, 0.7)',
            'negativeColor' => 'rgba(255, 79, 79, 0.7)'
        ]];
    }
}