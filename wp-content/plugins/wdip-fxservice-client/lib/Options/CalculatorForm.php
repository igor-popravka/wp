<?php

namespace WDIP\Plugin\Options;

use WDIP\Plugin\Plugin;
use WDIP\Plugin\Services;

/**
 * @property $categories
 * @property $series
 * @property $adminUrl
 * @property $serviceClient
 * @property $accountId
 * @property $amount
 * @property $fee
 * @property $start
 * @property $total_amount
 * @property $fee_amount
 * @property $gain_amount
 */
class CalculatorForm extends AbstractOptions {
    protected function generate(array $data) {
        $this->adminUrl = admin_url('admin-ajax.php');

        $data = $this->calculate($data);

        $this->total_amount = sprintf('$%s', number_format($data['total_amount'], 2));
        $this->gain_amount = sprintf('$%s', number_format($data['gain_amount'], 2));
        $this->fee_amount = sprintf('$%s', number_format($data['fee_amount'], 2));
        $this->categories = $data['categories'];
        $this->series = $data['series'];
    }

    protected function getData() {
        if (!empty($this->amount) && !empty($this->start) && !empty($this->fee)) {
            $result = [];
            if ($this->serviceClient == Plugin::SHORT_CODE_MYFXBOOK) {
                foreach ($this->accountId as $id) {
                    $result = array_merge($result, Services::model()->getMyFXBookMonthlyGainLossData($id));
                }

                $data = [];
                foreach ($result as $dt) {
                    $combine_data = array_combine($dt[0], $dt[1]);
                    foreach ($combine_data as $date => $val) {
                        if (isset($data[$date])) {
                            $data[$date][1] += $val;
                        } else {
                            $data[$date] = [
                                \DateTime::createFromFormat('M Y', $date)->getTimestamp(),
                                $val
                            ];
                        }
                    }
                };

                return $data;
            } else if ($this->serviceClient == Plugin::SHORT_CODE_FXBLUE) {
                return Services::model()->getFXBlueGrowthData($this->accountId);
            }
        }

        return [];
    }

    protected function getDefaultData() {
        return [
            'total_amount' => 0,
            'fee_amount' => 0,
            'gain_amount' => 0,
            'categories' => [],
            'series' => [
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
            ]
        ];
    }


    protected function calculate(array $data) {
        $result = $this->getDefaultData();

        if (!empty($data)) {
            $start_ts = \DateTime::createFromFormat('Y-m-d', $this->start)->getTimestamp();
            $data = array_filter($data, function ($val) use ($start_ts) {
                return $val[0] >= $start_ts;
            });

            $result['categories'] = array_keys($data);

            $in_amount = floatval($this->amount);
            $in_fee = floatval($this->fee);
            foreach ($data as $val) {
                $out_amount = round($in_amount * $val[1], 2);
                $out_fee = round($out_amount * $in_fee, 2);
                $out_total_amount = ($in_amount + $out_amount);

                $result['series'][0]['data'][] = $out_total_amount;
                $result['series'][1]['data'][] = $out_amount;
                $result['series'][2]['data'][] = $out_fee;
            }

            $result['total_amount'] = array_sum($result['series'][0]['data']);
            $result['gain_amount'] = array_sum($result['series'][1]['data']);
            $result['fee_amount'] = array_sum($result['series'][2]['data']);
        }

        return $result;
    }
}