<?php
namespace WDIP\Plugin\Options;

/**
 * @property $tableData
 */
class FXBlueAccountStatTable extends AbstractOptions {
    protected function generate(array $data) {
        $this->tableData = $data;
    }

    protected function getData() {
        return $this->getModel()->getFXBlueAccountStatData();
    }


}