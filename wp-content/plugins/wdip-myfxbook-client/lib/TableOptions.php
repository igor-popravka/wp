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
                $year = \DateTime::createFromFormat('M Y', $date)->format('Y');
                $month = strtoupper(\DateTime::createFromFormat('M Y', $date)->format('M'));
                $months[$month] = $val;
                $total += $val;
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

        foreach ($body as &$row) {
            $row['MONTHS'] = array_map(function ($val) {
                if ($val === 0) {
                    return '0.00%';
                } else if ($val !== 'N/A') {
                    return number_format($val, 2) . "%";
                }
                return $val;
            }, $row['MONTHS']);

            $row['TOT'] = number_format($row['TOT'], 2) . "%";
        }

        $this->tableData = [
            'BODY' => $body,
            'TOTAL_COMPOUNDED' => number_format($total_compounded, 2) . "%"
        ];
    }
}