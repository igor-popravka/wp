<?php
/** @var \WDIP\Plugin\Viewer $this */
/** @var \WDIP\Plugin\Options\CalculatorForm $options */

$options = $this->getOptions();
?>

<div id="<?= $options->uid; ?>" class="fxservice-calculator">
    <div class="data-panel">
        <form>
            <div class="control-grope">
                <label for="fxs-calc-invest-amount-<?= $options->uid; ?>">Investment amount</label>
                <input id="fxs-calc-invest-amount-<?= $options->uid; ?>" type="text" name="investAmount"
                       placeholder="0.00">
            </div>
            <div class="control-grope">
                <label for="fxs-calc-start-date-<?= $options->uid; ?>">Start date</label>
                <input id="fxs-calc-start-date-<?= $options->uid; ?>" type="text" name="startDate"
                       placeholder="yyyy-mm-dd">
            </div>
            <div class="control-grope">
                <label for="fxs-calc-performance-fee-<?= $options->uid; ?>">Performance fee</label>
                <select id="fxs-calc-performance-fee-<?= $options->uid; ?>" name="performanceFee">
                    <option value="0" selected>0%</option>
                </select>
            </div>

            <div class="button-grope">
                <input type="submit" value="Calculate" name="submit">
            </div>

            <div class="button-grope">
                <input type="button" value="Reset" name="reset">
            </div>

        </form>
    </div>

    <div class="chart-panel">
        <div id="fxs-calc-default-text-<?= $options->uid; ?>" style="padding-top: 2%">Nothing to display. Please change default configuration or enter filter data.</div>

        <div id="fxs-calc-spinner-<?= $options->uid; ?>" hidden><span class="spinner"></span></div>

        <div id="fxs-calc-charts-<?= $options->uid; ?>" hidden>
            <ul>
                <li><a href="#total-chart-<?= $options->uid; ?>">Total amount</a></li>
                <li><a href="#gl-chart-<?= $options->uid; ?>">Gain/Loss amount</a></li>
                <li><a href="#fee-chart-<?= $options->uid; ?>">Fee amount</a></li>
            </ul>
            <div id="total-chart-<?= $options->uid; ?>"></div>
            <div id="gl-chart-<?= $options->uid; ?>"></div>
            <div id="fee-chart-<?= $options->uid; ?>"></div>
        </div>
    </div>

    <div class="response-panel">
        <div class="control-grope border-right">
            <div class="control-item role-label">Total amount:</div>
            <div class="control-item role-text total-amount">0.00</div>
        </div>
        <div class="control-grope border-right">
            <div class="control-item role-label">Gain/Loss amount:</div>
            <div class="control-item role-text gain-loss-amount">0.00</div>
        </div>
        <div class="control-grope">
            <div class="control-item role-label">Total fee amount:</div>
            <div class="control-item role-text total-fee-amount">0.00</div>
        </div>
    </div>
</div>
<script>
    (function ($) {
        var options = JSON.parse('<?= $options->toJSON(); ?>');

        $('#<?= $options->uid ?>').FXServiceCalculator(options);
    })(jQuery);
</script>