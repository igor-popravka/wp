<?php
namespace WDIP\Plugin\Options;

use WDIP\Plugin\Plugin;
use WDIP\Plugin\Services;

/**
 * @property $tableData
 * @property $defaultcells
 * @property $serviceClient
 */
class MonthGrowthTable extends AbstractOptions {
    const NOT_AVAILABLE = 'N/A';

    protected function generate(array $data) {
        $this->generateDefaultCells();

        $body = [];
        foreach ($data['DATA'] as $dt) {
            $combine_data = array_combine($dt[0], $dt[1]);
            list($year, $months, $total) = $this->getDefaultTableRow();

            foreach ($combine_data as $date => $val) {
                if (preg_match('/^([a-z]{3})\s(\d{4})$/i', $date, $match) > 0) {
                    $year = $match[2];
                    $month = strtoupper($match[1]);
                    $months[$month] = $val;
                    $total += $val;
                }
            }

            $months = $this->mergeDefaultCells($year, $months);

            if (isset($body[$year])) {
                foreach ($months as $m => $v) {
                    if ($body[$year]['MONTHS'][$m] != self::NOT_AVAILABLE && $v != self::NOT_AVAILABLE) {
                        $body[$year]['MONTHS'][$m] += $v;
                        $body[$year]['TOT'] += $v;
                    } else if ($body[$year]['MONTHS'][$m] == self::NOT_AVAILABLE && $v != self::NOT_AVAILABLE) {
                        $body[$year]['MONTHS'][$m] = $v;
                        $body[$year]['TOT'] += $v;
                    }
                }
            } else {
                $body[$year] = [
                    'YEAR' => $year,
                    'MONTHS' => $months,
                    'TOT' => $total
                ];
            }
        };

        $this->tableData = array_merge($data, ['BODY' => $body]);
    }

    protected function getData() {
        $result = [
            'DATA' => [],
            'TOTAL_COMPOUNDED' => 0
        ];

        switch ($this->serviceClient){
            case Plugin::SHORT_CODE_MYFXBOOK:
                foreach ($this->accountId as $id) {
                    $result['DATA'] = array_merge($result['DATA'], Services::model()->getGainLossData($id));
                    $result['TOTAL_COMPOUNDED'] += Services::model()->getTotalGainData($id);
                }
                return $result;
            case Plugin::SHORT_CODE_FXBLUE:
                $result['DATA'] = Services::model()->getFXBlueChartData($this->chartType);
                $result['TOTAL_COMPOUNDED'] = Services::model()->getFXBlueChartData($this->chartType);
                return $result;
        }
        return $result;
    }

    public function getTickClass($value) {
        if ($value !== self::NOT_AVAILABLE) {
            $num = floatval($value);
            return $num >= 0 ? 'table-tick-green' : 'table-tick-red';
        }
        return $value;
    }

    public function format($value) {
        if ($value === 0) {
            return '0.00%';
        } else if ($value !== self::NOT_AVAILABLE) {
            return number_format($value, 2) . "%";
        }
        return $value;
    }

    public function formatMonthValue(array $data, $month) {
        $value = $data['MONTHS'][$month];
        return $this->format($value);
    }

    private function getDefaultTableRow() {
        return [
            self::NOT_AVAILABLE,
            [
                'JAN' => self::NOT_AVAILABLE,
                'FEB' => self::NOT_AVAILABLE,
                'MAR' => self::NOT_AVAILABLE,
                'APR' => self::NOT_AVAILABLE,
                'MAY' => self::NOT_AVAILABLE,
                'JUN' => self::NOT_AVAILABLE,
                'JUL' => self::NOT_AVAILABLE,
                'AUG' => self::NOT_AVAILABLE,
                'SEP' => self::NOT_AVAILABLE,
                'OCT' => self::NOT_AVAILABLE,
                'NOV' => self::NOT_AVAILABLE,
                'DEC' => self::NOT_AVAILABLE
            ],
            0
        ];
    }

    private function generateDefaultCells() {
        if (!empty($this->defaultcells)) {
            $default = explode(',', $this->defaultcells);
            $formatted = [];
            foreach ($default as $cell) {
                if (preg_match('/^\s*([a-z]{3})\s*:\s*(\d{4})\s*=\s*([0-9.]+)\s*$/i', $cell, $match)) {
                    if (!isset($formatted[$match[2]])) {
                        $formatted[$match[2]] = [];
                    }
                    $formatted[$match[2]][strtoupper($match[1])] = $match[3];
                }
            }
            $this->defaultcells = $formatted;
        }
    }

    private function mergeDefaultCells($year, array $months) {
        if (!empty($this->defaultcells)) {
            foreach ($this->defaultcells as $y => $m) {
                if ($year == $y) {
                    foreach ($m as $n => $v) {
                        if (isset($months[$n]) && $months[$n] == self::NOT_AVAILABLE) {
                            $months[$n] = $v;
                            continue;
                        }
                    }
                }
            }
        }
        return $months;
    }
}