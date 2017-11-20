<?php
namespace WDIP\Plugin\Options;

use WDIP\Plugin\Plugin;
use WDIP\Plugin\Services;

/**
 * @property $series
 * @property $monthTickInterval
 * @property $serviceClient
 */
class TotalGrowth extends AbstractOptions {
    protected function generate(array $data) {
        $this->monthTickInterval = 1000 * 3600 * 24 * 30;

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
        switch ($this->serviceClient){
            case Plugin::SHORT_CODE_MYFXBOOK:
                $basic = 0;
                $result = [];
                foreach ($this->accountId as $id) {
                    $result = array_merge($result, Services::model()->getMyFXBookGrowthData($id, $basic));
                    $basic = $result[count($result) - 1][1];
                }
                return $result;
            case Plugin::SHORT_CODE_FXBLUE:
                return Services::model()->getFXBlueGrowthData($this->accountId);
        }
        return [];
    }
}