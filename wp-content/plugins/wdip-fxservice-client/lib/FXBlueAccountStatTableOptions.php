<?php
namespace WDIP\Plugin;

/**
 * @property $tableData
 */
class FXBlueAccountStatTableOptions extends MyFXBookOptions {
    protected function generate() {
        $this->tableData = $this->getModel()->getFXBlueAccountStatData();
    }
}