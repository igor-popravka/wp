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
        $tableData = [
            'header' => ['YEAR', 'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEPT', 'OCT', 'NOV', 'DEC', 'TOT'],
            'body' => [],
            'footer' => ['TOTAL COMPOUNDED RETURN' => 0]
        ];

        foreach ($this->accountid as $id) {

        }
    }
}