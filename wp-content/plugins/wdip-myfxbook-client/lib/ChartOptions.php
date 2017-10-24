<?php
namespace WDIP\Plugin;

use WDIP\Plugin\MyFXBookClient as MFBClient;
use WDIP\Plugin\MyFXBookConfig as MFBConfig;

/**
 * @property $uid
 * @property $charttype
 * @property $title
 * @property $chartheight
 * @property $chartwidth
 * @property $accountid
 * @property $backgroundcolor
 * @property $gridlinecolor
 * @property $monthtickinterval
 * @property $series
 * @property $seriesData
 * @property MyFXBookData $CalcFormOptions
 * @property $fee
 * @property $adminUrl
 * @property $categories
 */
class ChartOptions extends MyFXBookData {
    public function __construct(MyFXBookData $options) {
        parent::__construct($options);

        $this->monthtickinterval = 1000 * 3600 * 24 * 30;

        $this->generateSeries();
    }

    private function generateSeries() {
        $this->series = [];
        switch ($this->charttype) {
            case MFBClient::TYPE_MONTH_GROWTH:
                $series_data = [];

                foreach ($this->accountid as $id) {
                    $series_data = $this->addMonthGrowthSeries($id);
                }

                $this->series = [[
                    'name' => 'Quest',
                    'data' => array_values($series_data),
                    'color' => 'rgba(124, 181, 236, 0.7)',
                    'negativeColor' => 'rgba(255, 79, 79, 0.7)'
                ]];

                break;
            case MFBClient::TYPE_TOTAL_GROWTH:
                foreach ($this->accountid as $id) {
                    $this->addTotalGrowthSeries($id);
                }
                break;
            case MFBClient::TYPE_MONTHLY_GAIN_LOSS:
                $xy_data = [];

                foreach ($this->accountid as $id) {
                    $xy_data = $this->addMonthlyGainLossSeries($id);
                }

                $this->series = [[
                    'name' => 'Quest',
                    'data' => array_values($xy_data),
                    'color' => 'rgba(124, 181, 236, 0.7)',
                    'negativeColor' => 'rgba(255, 79, 79, 0.7)'
                ]];

                break;
            case MFBClient::TYPE_CALCULATOR_FORM:
                $this->CalcFormOptions = new MyFXBookData(MyFXBookConfig::instance()->CALCULATOR_FORM);
                $this->adminUrl = admin_url('admin-ajax.php');
                $this->addCalculatorFormSeries();
        }
    }

    private function addCalculatorFormSeries() {
        $this->series = [
            [
                "name" => "Total",
                "data" => [],
                "color" => "#2D8AC7"
            ],
            [
                "name" => "Gain",
                "data" => [],
                "color" => "#7CA821"
            ],
            [
                "name" => "Fee",
                "data" => [],
                "color" => "#A94442"

            ]
        ];
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

    private function addTotalGrowthSeries($account_id) {
        if ($account_info = MFBClient::instance()->getAccountInfo($account_id)) {
            if (MFBClient::instance()->getEnvironment() == MFBClient::ENV_DEV) {
                $result = MFBClient::instance()->getDataFromJSON("myfxbook.get-daily-gain-{$account_id}", true);
            } else {
                $daily_gain = MFBConfig::instance()->SERIES->daily_gain;
                $result = MFBClient::instance()->httpRequest($daily_gain->url, [
                    'session' => MFBClient::instance()->getSession(),
                    'id' => $account_id,
                    'start' => \DateTime::createFromFormat('m/d/Y H:i', $account_info->firstTradeDate)->format('Y-m-d'),
                    'end' => (new \DateTime())->format('Y-m-d')
                ]);
            }

            if (!$result->error) {
                $dailyGainData = [];
                foreach ($result->dailyGain as $data) {
                    $utc = \DateTime::createFromFormat("m/d/Y", $data[0]->date)->getTimestamp() * 1000;

                    if (!isset($dailyGainData["uts_{$utc}"])) {
                        $dailyGainData["uts_{$utc}"] = ['uts' => $utc, 'value' => []];
                    }
                    $dailyGainData["uts_{$utc}"]['value'] [] = $data[0]->value;
                }

                if (empty($this->series)) {
                    $series = [[
                        'name' => 'Quest',
                        'data' => [],
                        'color' => 'rgba(124, 181, 236, 0.7)',
                        'negativeColor' => 'rgba(255, 79, 79, 0.7)'
                    ]];

                    $start_value = 0;
                } else {
                    $series = $this->series;
                    $start_value = $series[0]['data'][count($series[0]['data']) - 1][1];
                }

                foreach ($dailyGainData as $data) {
                    $series[0]['data'][] = [
                        $data['uts'],
                        ($start_value + (min($data['value']) < 0 ? min($data['value']) : max($data['value'])))
                    ];
                }

                $this->series = $series;
            }
        }
    }

    private function addMonthlyGainLossSeries($account_id) {
        static $xy_data = [];

        if ($account_info = MFBClient::instance()->getAccountInfo($account_id)) {
            $monthly_gain_los = MFBConfig::instance()->SERIES->monthly_gain_los;
            $startYear = intval(\DateTime::createFromFormat('m/d/Y H:i', $account_info->firstTradeDate)->format('Y'));
            $endYear = intval(\DateTime::createFromFormat('m/d/Y H:i', $account_info->lastUpdateDate)->format('Y'));

            while ($startYear <= $endYear) {
                $result = MFBClient::instance()->httpRequest($monthly_gain_los->url, [
                    'chartType' => 3,
                    'monthType' => 0,
                    'accountOid' => $account_id,
                    'startDate' => "{$startYear}-01-01",
                    'endDate' => (new \DateTime())->format('Y-m-d')
                ]);
                if (isset($result->categories) && isset($result->series)) {
                    foreach ($result->categories as $index => $name) {
                        $utc = \DateTime::createFromFormat('M Y', $name)
                                ->modify('last day of this month')
                                ->getTimestamp() * 1000;
                        $value = array_shift($result->series[0]->data[$index]);

                        if (isset($xy_data[$name])) {
                            $xy_data[$name][1] += $value;
                        } else {
                            $xy_data[$name] = [
                                $utc,
                                $value
                            ];
                        }
                    }
                }
                $startYear++;
            }
        }
        return $xy_data;
    }
}