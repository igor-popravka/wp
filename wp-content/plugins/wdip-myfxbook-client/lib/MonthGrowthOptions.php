<?php
namespace WDIP\Plugin;

use WDIP\Plugin\MyFXBookClient as MFBClient;
use WDIP\Plugin\MyFXBookConfig as MFBConfig;

/**
 * @property $series
 * @property $monthtickinterval
 */
class MonthGrowthOptions extends MyFXBookOptions {
    protected function generate() {
        $this->monthtickinterval = 1000 * 3600 * 24 * 30;
        $data = [];
        $basic = 0;
        foreach ($this->accountid as $id) {
            $data = array_merge($data, $this->getModel()->getGrowthData($id, $basic));
            $basic = $data[count($data)-1][1];
        }

        /*$this->series = [[
            'name' => 'Quest',
            'data' => array_values($data),
            'color' => 'rgba(124, 181, 236, 0.7)',
            'negativeColor' => 'rgba(255, 79, 79, 0.7)'
        ]];*/

        $this->series = $data;
    }

    private function addMonthGrowthSeries($account_id) {
        static $series_data = [];

        if ($account_info = MFBClient::instance()->getAccountInfo($account_id)) {
            if (MFBClient::instance()->getEnvironment() == MFBClient::ENV_DEV) {
                $result = MFBClient::instance()->getDataFromJSON("myfxbook.get-daily-gain-{$account_id}", true);
            } else {
                $daily_gain = MFBConfig::instance()->SERIES->daily_gain;
                $result = MFBClient::instance()->httpRequest($daily_gain->url, [
                    'session' => MFBClient::instance()->getSession(),
                    'id' => $account_id,
                    'start' => \DateTime::createFromFormat('m/d/Y H:i', $account_info->firstTradeDate)->format('Y-m-d'),
                    'end' => \DateTime::createFromFormat('m/d/Y H:i', $account_info->lastUpdateDate)->format('Y-m-d')
                ]);
            }

            if (!$result->error) {
                if (!empty($series_data)) {
                    end($series_data);
                    $start_value = $series_data[key($series_data)][1];
                    reset($series_data);
                } else {
                    $start_value = 0;
                }

                foreach ($result->dailyGain as $data) {
                    $date = \DateTime::createFromFormat("m/d/Y", $data[0]->date);
                    $name = $date->format('M Y');

                    $series_data[$name] = [
                        $date->getTimestamp() * 1000,
                        $start_value + $data[0]->value
                    ];
                }
            }
        }

        return $series_data;
    }
}