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
 * @property MyFXBookData $CalcFormOptions
 * @property $fee
 * @property $adminUrl
 */
class ChartOptions extends MyFXBookData {
    private static $count = 0;

    public function __construct(MyFXBookData $options) {
        parent::__construct($options);

        $this->monthtickinterval = 1000 * 3600 * 24 * 30;

        $this->generateUID();
        $this->generateSeries();
    }

    private function generateUID() {
        $uid_string = sprintf("%s-%s-%d-%d", __CLASS__, $this->charttype, time(), self::$count++);
        $this->uid = md5($uid_string);
    }

    private function generateSeries() {
        $this->series = [];
        switch ($this->charttype) {
            case MFBClient::TYPE_MONTH_GROWTH:
            case MFBClient::TYPE_TOTAL_GROWTH:
                foreach ($this->accountid as $index => $id) {
                    $this->addGrowthSeries($index, $id);
                }
                break;
            case MFBClient::TYPE_MONTHLY_GAIN_LOSS:
                foreach ($this->accountid as $index => $id) {
                    $this->addMonthlyGainLossSeries($index, $id);
                }
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

    private function addGrowthSeries($index, $account_id) {
        if ($account_info = MFBClient::instance()->getAccountInfo($account_id)) {
            $daily_gain = MFBConfig::instance()->SERIES->daily_gain;
            $result = MFBClient::instance()->httpRequest($daily_gain->url, [
                'session' => MFBClient::instance()->getSession(),
                'id' => $account_id,
                'start' => \DateTime::createFromFormat('m/d/Y H:i', $account_info->firstTradeDate)->format('Y-m-d'),
                'end' => (new \DateTime())->format('Y-m-d')
            ]);

            if (!$result->error) {
                $series = [
                    'name' => $account_info->name,
                    'data' => [],
                    'color' => $daily_gain->color[$index],
                    'negativeColor' => $daily_gain->negativeColor[$index]
                ];

                $dailyGainData = [];
                foreach ($result->dailyGain as $data) {
                    if ($this->charttype == MFBClient::TYPE_MONTH_GROWTH) {
                        $date = preg_replace("/(\d{2})\/\d{2}\/(\d{4})/", '$1/01/$2', $data[0]->date);
                        $utc = \DateTime::createFromFormat("m/01/Y", $date)->getTimestamp() * 1000;
                    } else {
                        $utc = \DateTime::createFromFormat("m/d/Y", $data[0]->date)->getTimestamp() * 1000;
                    }

                    if (!isset($dailyGainData["uts_{$utc}"])) {
                        $dailyGainData["uts_{$utc}"] = ['uts' => $utc, 'value' => []];
                    }
                    $dailyGainData["uts_{$utc}"]['value'] [] = $data[0]->value;
                }

                foreach ($dailyGainData as $data) {
                    $series['data'][] = [
                        $data['uts'],
                        min($data['value']) < 0 ? min($data['value']) : max($data['value'])
                    ];
                }

                $origin_series = $this->series;
                $origin_series[] = $series;
                $this->series = $origin_series;
            }
        }
    }

    private function addMonthlyGainLossSeries($index, $account_id) {
        if ($account_info = MFBClient::instance()->getAccountInfo($account_id)) {
            $monthly_gain_los = MFBConfig::instance()->SERIES->monthly_gain_los;

            $series = [
                'name' => $account_info->name,
                'data' => [],
                'color' => $monthly_gain_los->color[$index],
                'negativeColor' => $monthly_gain_los->negativeColor[$index]
            ];
            $series_data = [];

            $countYear = \DateTime::createFromFormat('m/d/Y H:i', $account_info->firstTradeDate)->format('Y');
            $endYear = (new \DateTime())->format('Y');
            $endDate = (new \DateTime())->modify('last day of this month')->format('Y-m-d');
            while ($countYear <= $endYear) {
                $result = MFBClient::instance()->httpRequest($monthly_gain_los->url, [
                    'chartType' => 3,
                    'monthType' => 0,
                    'accountOid' => $account_id,
                    'startDate' => "{$countYear}-01-01",
                    'endDate' => $endDate
                ]);
                if (isset($result->categories) && isset($result->series)) {
                    $keys = array_map(function ($val) {
                        $val = sprintf('01-%s', str_replace(' ', '-', $val));
                        return \DateTime::createFromFormat('d-M-Y', $val)->getTimestamp() * 1000;
                    }, $result->categories);
                    $values = array_map(function ($item) {
                        return array_shift($item);
                    }, $result->series[0]->data);
                    $series_data = array_merge($series_data, array_combine($keys, $values));
                }
                $countYear++;
            }

            foreach ($series_data as $utc => $value) {
                $series['data'][] = [
                    $utc * 1,
                    $value
                ];
            }

            $origin_series = $this->series;
            $origin_series[] = $series;
            $this->series = $origin_series;
        }
    }
}