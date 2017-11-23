<?php

namespace WDIP\Plugin\Options;

use WDIP\Plugin\Plugin;
use WDIP\Plugin\Services;

/**
 * @property $adminUrl
 * @property $serviceClient
 * @property $calculate
 *
 * @property $accountId
 * @property $feeList
 *
 * @property $investAmount
 * @property $interestRate
 * @property $startDate
 *
 * @property $totalAmount
 * @property $feeAmount
 * @property $gainLosAmount
 *
 * @property $chartOptions
 */
class CalculatorForm extends AbstractOptions {
    protected function generate(array $data) {
        $this->adminUrl = admin_url('admin-ajax.php');

        $data = $this->calculate($data);

        $chart_options = Services::config()->CALCULATOR_CHART_OPTIONS;
        $chart_options['xAxis']['categories'] = $data['categories'];

        $series = json_decode('[' . implode(',', (array)$chart_options['series']) . ']', true);
        $series[0]['data'] = $data['total_series_data'];
        $series[1]['data'] = $data['gain_los_series_data'];
        $series[2]['data'] = $data['fee_series_data'];
        $chart_options['series'] = $series;

        if (!empty($this->title)) {
            $chart_options['title']['text'] = $this->title;
        }

        if (!empty($this->backgroundColor)) {
            $chart_options['chart']['backgroundColor'] = $this->backgroundColor;
        }

        if (!empty($this->gridLineColor)) {
            $chart_options['yAxis']['gridLineColor'] = $this->gridLineColor;
        }

        $this->chartOptions = $chart_options;

        $this->totalAmount = $data['total_amount'];
        $this->gainLosAmount = $data['gain_los_amount'];
        $this->feeAmount = $data['fee_amount'];
    }

    protected function getData() {
        if (isset($this->calculate) && $this->calculate) {
            $result = [];
            if ($this->serviceClient == Plugin::SHORT_CODE_MYFXBOOK) {
                try {
                    foreach ($this->accountId as $id) {
                        $result = array_merge($result, Services::model()->getMyFXBookMonthlyGainLossData($id));
                    }
                } catch (\Exception $e) {
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
                try {
                    foreach ($this->accountId as $id) {
                        $result = array_merge($result, Services::model()->getFXBlueMonthlyGainLossData($id));
                    }
                } catch (\Exception $e) {
                }

                $data = [];
                foreach ($result as $val) {
                    $date = \DateTime::createFromFormat('m/d/Y', $val[0]);
                    $data[$date->format('M Y')] = [$date->getTimestamp(), floatval($val[1])];
                };

                return $data;
            }
        }

        return [];
    }

    protected function calculate(array $data) {
        $result = [
            'total_amount' => 0,
            'gain_los_amount' => 0,
            'fee_amount' => 0,
            'categories' => [],
            'total_series_data' => [],
            'gain_los_series_data' => [],
            'fee_series_data' => []
        ];

        if (!empty($data)) {
            $start_ts = \DateTime::createFromFormat('Y-m-d', $this->startDate)->getTimestamp();
            $data = array_filter($data, function ($val) use ($start_ts) {
                return $val[0] >= $start_ts;
            });

            $result['categories'] = array_keys($data);

            $amount = floatval($this->investAmount);
            $fee = floatval($this->interestRate);
            foreach ($data as $val) {
                $gain_amount = round(($amount * ($val[1] / 100)), 2);
                $fee_amount = round(abs($gain_amount) * $fee, 2);
                $amount = ($amount + $gain_amount - $fee_amount);

                $result['total_series_data'][] = $amount;
                $result['gain_los_series_data'][] = $gain_amount;
                $result['fee_series_data'][] = $fee_amount;
            }

            $result['total_amount'] = $result['total_series_data'][count($result['total_series_data']) - 1];
            $result['gain_los_amount'] = array_sum($result['gain_los_series_data']);
            $result['fee_amount'] = array_sum($result['fee_series_data']);
        }

        return $result;
    }
}