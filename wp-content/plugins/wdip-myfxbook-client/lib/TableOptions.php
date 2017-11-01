<?php
namespace WDIP\Plugin;

/**
 * @author: igor.popravka
 * Date: 24.10.2017
 * Time: 13:18
 *
 * @property $tableData
 */
class TableOptions extends MyFXBookOptions {
    protected function generate() {
        $data = [];
        $total_compounded = 0;
        foreach ($this->accountid as $id) {
            $data = array_merge($data, $this->getModel()->getGainLossData($id));
            $total_compounded += $this->getModel()->getTotalGainData($id);
        }

        $body = [];
        foreach ($data as $dt) {
            $combine_data = array_combine($dt[0], $dt[1]);
            $year = 'N/A';
            $months = ['JAN' => 'N/A', 'FEB' => 'N/A', 'MAR' => 'N/A', 'APR' => 'N/A', 'MAY' => 'N/A', 'JUN' => 'N/A', 'JUL' => 'N/A', 'AUG' => 'N/A', 'SEP' => 'N/A', 'OCT' => 'N/A', 'NOV' => 'N/A', 'DEC' => 'N/A'];
            $total = 0;
            foreach ($combine_data as $date => $val) {
                if (preg_match('/^([a-z]{3})\s(\d{4})$/i', $date, $match) > 0) {
                    $year = $match[2];
                    $month = strtoupper($match[1]);
                    $months[$month] = $val;
                    $total += $val;
                }
            }

            if (isset($body[$year])) {
                foreach ($months as $m => $v) {
                    if ($body[$year]['MONTHS'][$m] != 'N/A' && $v != 'N/A') {
                        $body[$year]['MONTHS'][$m] += $v;
                        $body[$year]['TOT'] += $v;
                    } else if ($body[$year]['MONTHS'][$m] == 'N/A' && $v != 'N/A') {
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

        $this->tableData = [
            'BODY' => $body,
            'TOTAL_COMPOUNDED' => $total_compounded
        ];
    }

    public function getTickClass($value) {
        if($value !== 'N/A'){
            $num = floatval($value);
            return $num >= 0 ? 'table-tick-green' : 'table-tick-red';
        }
        return $value;
    }

    public function format($value) {
        if ($value === 0) {
            return '0.00%';
        } else if ($value !== 'N/A') {
            return number_format($value, 2) . "%";
        }
        return $value;
    }

    public function formatMonthValue(array $data, $month) {
        $value = $data['MONTHS'][$month];
        return $this->format($value);
    }
}