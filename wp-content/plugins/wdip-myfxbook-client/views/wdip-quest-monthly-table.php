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
            <td><?php echo $row['MONTHS']['JAN'] != 'N/A' ? $row['MONTHS']['JAN'] . "%" : $row['MONTHS']['JAN']; ?></td>
            <td><?php echo $row['MONTHS']['JAN'] != 'N/A' ? $row['MONTHS']['FEB'] . "%" : $row['MONTHS']['FEB']; ?></td>
            <td><?php echo $row['MONTHS']['JAN'] != 'N/A' ? $row['MONTHS']['MAR'] . "%" : $row['MONTHS']['MAR']; ?></td>
            <td><?php echo $row['MONTHS']['JAN'] != 'N/A' ? $row['MONTHS']['APR'] . "%" : $row['MONTHS']['APR']; ?></td>
            <td><?php echo $row['MONTHS']['JAN'] != 'N/A' ? $row['MONTHS']['MAY'] . "%" : $row['MONTHS']['MAY']; ?></td>
            <td><?php echo $row['MONTHS']['JAN'] != 'N/A' ? $row['MONTHS']['JUN'] . "%" : $row['MONTHS']['JUN']; ?></td>
            <td><?php echo $row['MONTHS']['JAN'] != 'N/A' ? $row['MONTHS']['JUL'] . "%" : $row['MONTHS']['JUL']; ?></td>
            <td><?php echo $row['MONTHS']['JAN'] != 'N/A' ? $row['MONTHS']['AUG'] . "%" : $row['MONTHS']['AUG']; ?></td>
            <td><?php echo $row['MONTHS']['JAN'] != 'N/A' ? $row['MONTHS']['SEP'] . "%" : $row['MONTHS']['SEP']; ?></td>
            <td><?php echo $row['MONTHS']['JAN'] != 'N/A' ? $row['MONTHS']['OCT'] . "%" : $row['MONTHS']['OCT']; ?></td>
            <td><?php echo $row['MONTHS']['JAN'] != 'N/A' ? $row['MONTHS']['NOV'] . "%" : $row['MONTHS']['NOV']; ?></td>
            <td><?php echo $row['MONTHS']['JAN'] != 'N/A' ? $row['MONTHS']['DEC'] . "%" : $row['MONTHS']['DEC']; ?></td>
            <td><?php echo $row['TOT'] != 'N/A' ? $row['TOT'] . "%" : $row['TOT']; ?></td>
        </tr>
    <?php endforeach; ?>
    <tr>
        <td colspan="13" style="padding-left: 1.5%; font-weight: bold; text-align:left;">TOTAL COMPOUNDED RETURN</td>
        <td style="font-weight: bold; text-align:left;"><?php echo $options->tableData['TOTAL_COMPOUNDED']; ?>%</td>
    </tr>
</table>
