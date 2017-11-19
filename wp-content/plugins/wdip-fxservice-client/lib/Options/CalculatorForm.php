<?php
namespace WDIP\Plugin\Options;

use WDIP\Plugin\Plugin;
use WDIP\Plugin\Services;

/**
 * @property $series
 * @property $adminUrl
 * @property $serviceClient
 * @property $accountId
 * @property $amount
 * @property $fee
 * @property $start
 */
class CalculatorForm extends AbstractOptions {
    protected function generate(array $data) {
        $this->adminUrl = admin_url('admin-ajax.php');
        $this->series = $data;
    }

    protected function getData() {
        return [
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

    public function calculate() {
        $result = [];
        $data = [
            'total_amount' => '$0.00',
            'fee_amount' => '$0.00',
            'gain_amount' => '$0.00',
            'series' => [
                'categories' => [],
                'total_amount_data' => [],
                'fee_amount_data' => [],
                'gain_amount_data' => []
            ]
        ];

        if ($this->serviceClient == Plugin::SHORT_CODE_MYFXBOOK) {
            $basic = 0;
            foreach ($this->accountId as $id) {
                $result = array_merge($result, Services::model()->getMyFXBookGrowthData($id, $basic));
                $basic = $result[count($result) - 1][1];
            }
        } else if ($this->serviceClient == Plugin::SHORT_CODE_FXBLUE) {
            $result = Services::model()->getFXBlueGrowthData($this->accountId);
        }

        if (!empty($result)) {
            $start_ts = \DateTime::createFromFormat('Y-m-d', $this->start)->getTimestamp();
            $result = array_map(function ($row) use ($start_ts) {
                $current_ts = \DateTime::createFromFormat('m/d/Y', $row[0])->getTimestamp();
                return $start_ts >= $current_ts;
            }, $result);
            
            $amount = floatval($this->amount);
            $fee = floatval($this->fee);
            $total_amount = $fee_amount = $gain = 0;

            foreach ($result as $item) {
                $name = $item[0];
                if (empty($data['series']['categories'])) {
                    $gain = 0;
                } else {
                    $gain = round($amount * ($item[1] / 100), 2);
                }
                $total_amount = ($amount + $gain);
                $fee_amount = round($gain * $fee, 2);
                $data['series']['categories'][] = $name;
                $data['series']['total_amount_data'][] = $total_amount;
                $data['series']['gain_amount_data'][] = $gain;
                $data['series']['fee_amount_data'][] = $fee_amount;
            }
            $data['total_amount'] = '$' . $total_amount;
            $data['fee_amount'] = '$' . $fee_amount;
            $data['gain_amount'] = '$' . $gain;
        }

        return $data;
    }
}