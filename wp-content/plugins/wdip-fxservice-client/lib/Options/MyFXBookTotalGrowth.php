<?php
namespace WDIP\Plugin\Options;

/**
 * @property $series
 * @property $monthtickinterval
 */
class MyFXBookTotalGrowth extends AbstractOptions {
    protected function generate(array $data) {
        $this->monthtickinterval = 1000 * 3600 * 24 * 30;

        $data = array_map(function($val){
            return [
                \DateTime::createFromFormat("m/d/Y", $val[0])->getTimestamp() * 1000,
                $val[1]
            ];
        }, $data);

        $this->series =  [[
            'name' => 'Quest',
            'data' => $data,
            'color' => 'rgba(124, 181, 236, 0.7)',
            'negativeColor' => 'rgba(255, 79, 79, 0.7)'
        ]];
    }

    protected function getData() {
        $result = [];
        $basic = 0;
        foreach ($this->accountid as $id) {
            $result = array_merge($result, $this->getModel()->getGrowthData($id, $basic));
            $basic = $result[count($result) - 1][1];
        }
        return $result;
    }
}