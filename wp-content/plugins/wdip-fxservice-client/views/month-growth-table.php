<?php
/** @var \WDIP\Plugin\Viewer $this */
/** @var \WDIP\Plugin\Options\MonthGrowthTable $options */

$options = $this->getOptions();
?>

<table class="chart quest-monthly-table">
    <tr>
        <th>YEAR</th>
        <th>JAN</th>
        <th>FEB</th>
        <th>MAR</th>
        <th>APR</th>
        <th>MAY</th>
        <th>JUN</th>
        <th>JUL</th>
        <th>AUG</th>
        <th>SEP</th>
        <th>OCT</th>
        <th>NOV</th>
        <th>DEC</th>
        <th>TOT</th>
    </tr>
    <?php foreach ($options->tableData['BODY'] as $row): ?>
        <tr>
            <td><b><?php echo $row['YEAR']; ?></b></td>
            <td class="<?php echo $options->getTickClass($row['MONTHS']['JAN']); ?>"><?php echo $options->formatMonthValue($row, 'JAN'); ?></td>
            <td class="<?php echo $options->getTickClass($row['MONTHS']['FEB']); ?>"><?php echo $options->formatMonthValue($row, 'FEB'); ?></td>
            <td class="<?php echo $options->getTickClass($row['MONTHS']['MAR']); ?>"><?php echo $options->formatMonthValue($row, 'MAR'); ?></td>
            <td class="<?php echo $options->getTickClass($row['MONTHS']['APR']); ?>"><?php echo $options->formatMonthValue($row, 'APR'); ?></td>
            <td class="<?php echo $options->getTickClass($row['MONTHS']['MAY']); ?>"><?php echo $options->formatMonthValue($row, 'MAY'); ?></td>
            <td class="<?php echo $options->getTickClass($row['MONTHS']['JUN']); ?>"><?php echo $options->formatMonthValue($row, 'JUN'); ?></td>
            <td class="<?php echo $options->getTickClass($row['MONTHS']['JUL']); ?>"><?php echo $options->formatMonthValue($row, 'JUL'); ?></td>
            <td class="<?php echo $options->getTickClass($row['MONTHS']['AUG']); ?>"><?php echo $options->formatMonthValue($row, 'AUG'); ?></td>
            <td class="<?php echo $options->getTickClass($row['MONTHS']['SEP']); ?>"><?php echo $options->formatMonthValue($row, 'SEP'); ?></td>
            <td class="<?php echo $options->getTickClass($row['MONTHS']['OCT']); ?>"><?php echo $options->formatMonthValue($row, 'OCT'); ?></td>
            <td class="<?php echo $options->getTickClass($row['MONTHS']['NOV']); ?>"><?php echo $options->formatMonthValue($row, 'NOV'); ?></td>
            <td class="<?php echo $options->getTickClass($row['MONTHS']['DEC']); ?>"><?php echo $options->formatMonthValue($row, 'DEC'); ?></td>
            <td><b><?php echo $options->format($row['TOT']); ?></b></td>
        </tr>
    <?php endforeach; ?>
    <tr>
        <td colspan="13"><b style="float: left; margin-left: 18px">TOTAL COMPOUNDED RETURN</b></td>
        <td><b><?php echo $options->format($options->tableData['TOTAL_COMPOUNDED']); ?></b></td>
    </tr>
</table>
