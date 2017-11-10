<?php
/** @var \WDIP\Plugin\Viewer $this */
/** @var \WDIP\Plugin\FXBlueAccountStatTableOptions $options */

$options = $this->getOptions();
?>

<table class="chart">
    <tr>
        <th>Total return:</th>
        <td><?php echo round($options->tableData->totalReturn, 2); ?></td>
    </tr>
    <tr>
        <th>Monthly return:</th>
        <td><?php echo round($options->tableData->monthlyReturn, 2); ?></td>
    </tr>
    <tr>
        <th>Weekly return:</th>
        <td><?php echo round($options->tableData->weeklyReturn, 2); ?></td>
    </tr>
    <tr>
        <th>Peak drawdown:</th>
        <td><?php echo round($options->tableData->deepestBalanceValley, 2); ?></td>
    </tr>
    <tr>
        <th>Trade win %:</th>
        <td><?php echo round($options->tableData->tradeWinPercent, 2); ?></td>
    </tr>
    <tr>
        <th>Profit factor:</th>
        <td><?php echo round($options->tableData->profitFactor, 2); ?></td>
    </tr>
    <tr>
        <th>Pips:</th>
        <td><?php echo round($options->tableData->pips, 2); ?></td>
    </tr>
    <tr>
        <th>Trades per day:</th>
        <td><?php echo round($options->tableData->tradesPerDay, 2); ?></td>
    </tr>
    <tr>
        <th>History:</th>
        <td><?php echo "{$options->tableData->dayCount} days"; ?></td>
    </tr>
</table>
