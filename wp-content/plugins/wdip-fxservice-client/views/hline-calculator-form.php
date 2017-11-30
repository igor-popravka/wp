<?php
/** @var \WDIP\Plugin\Viewer $this */
/** @var \WDIP\Plugin\Options\CalculatorForm $options */

$options = $this->getOptions();
?>

<div id="<?= $options->uid; ?>" class="fxservice-calculator">
    <div class="data-panel">
        <form>
            <div class="control-grope">
                <label>Investment amount</label>
                <input type="text" name="investAmount" placeholder="0.00">
            </div>
            <div class="control-grope">
                <label>Start date</label>
                <input type="text" name="startDate" placeholder="yyyy-mm-dd">
            </div>
            <div class="control-grope">
                <label>Performance fee</label>
                <select name="performanceFee">
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

    <div class="chart-panel">Calculation Result Graph</div>

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