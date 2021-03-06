<?php
namespace WDIP\Plugin\Options;

use WDIP\Plugin\Plugin;
use WDIP\Plugin\Services;

/**
 * @property $series
 * @property $categories
 * @property $serviceClient
 */
class MonthlyGainLoss extends AbstractOptions {
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

        switch ($this->serviceClient) {
            case Plugin::SHORT_CODE_MYFXBOOK:
                try {
                    foreach ($this->accountId as $id) {
                        $result = array_merge($result, Services::model()->getMyFXBookMonthlyGainLossData($id));
                    }
                } catch (\Exception $e) {
                }

                return $result;
            case Plugin::SHORT_CODE_FXBLUE:
                try {
                    foreach ($this->accountId as $id) {
                        $result = Services::model()->getFXBlueMonthlyGainLossData($id);
                    }

                    $result = array_map(function ($val) {
                        $date = \DateTime::createFromFormat("m/d/Y", $val[0])->format('M Y');
                        return [[$date], [$val[1]]];
                    }, $result);
                } catch (\Exception $e) {
                }
                
                return $result;
        }
        return $result;
    }
}