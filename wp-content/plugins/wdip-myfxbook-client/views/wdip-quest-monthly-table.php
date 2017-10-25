<?php
/** @var \WDIP\Plugin\Viewer $this */
/** @var \WDIP\Plugin\TableOptions $options */

$options = $this->getOptions();
?>

<table class="chart">
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
            <td><?php echo $row['YEAR']; ?></td>
            <td><?php echo $row['MONTHS']['JAN']; ?></td>
            <td><?php echo $row['MONTHS']['FEB']; ?></td>
            <td><?php echo $row['MONTHS']['MAR']; ?></td>
            <td><?php echo $row['MONTHS']['APR']; ?></td>
            <td><?php echo $row['MONTHS']['MAY']; ?></td>
            <td><?php echo $row['MONTHS']['JUN']; ?></td>
            <td><?php echo $row['MONTHS']['JUL']; ?></td>
            <td><?php echo $row['MONTHS']['AUG']; ?></td>
            <td><?php echo $row['MONTHS']['SEP']; ?></td>
            <td><?php echo $row['MONTHS']['OCT']; ?></td>
            <td><?php echo $row['MONTHS']['NOV']; ?></td>
            <td><?php echo $row['MONTHS']['DEC']; ?></td>
            <td><?php echo $row['TOT']; ?></td>
        </tr>
    <?php endforeach; ?>
    <tr>
        <td colspan="13"><b>TOTAL COMPOUNDED RETURN</b></td>
        <td><b><?php echo $options->tableData['TOTAL_COMPOUNDED']; ?>%</b></td>
    </tr>
</table>
