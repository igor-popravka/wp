<?php
namespace WDIP\Plugin;

/**
 * @author: igor.popravka
 * Date: 24.10.2017
 * Time: 13:18
 *
 * @property $uid
 * @property $tableData
 * @property $accountid
 */
class TableOptions extends MyFXBookData {
    public function __construct(MyFXBookData $options) {
        parent::__construct($options);
        $this->generateTableData();
    }

    private function generateTableData() {
        $data = [];
        foreach ($this->accountid as $id) {
            $data = array_merge($data, $this->getModel()->getMonthlyGainLossData($id));
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

        $total_compounded = 0;
        foreach ($body as $row) {
            $total_compounded += $row['TOT'];
        }

        $this->tableData = [
            'BODY' => $body,
            'TOTAL_COMPOUNDED' => $total_compounded
        ];
    }
}