<div id="<?php echo $code; ?>" class="wdip-calc-wrapper">
    <div class="wdip-result">
        <div>
            <label>Total amount:</label>
            <span class="wdip-field-total" name="wdip_total_amount">$0.00</span>
        </div>
        <div>
            <label>Gain amount:</label>
            <span class="wdip-field-gain" name="wdip_gain_amount">$0.00</span>
        </div>
        <div>
            <label>Fee amount:</label>
            <span class="wdip-field-fee" name="wdip_fee_amount">$0.00</span>
        </div>
    </div>
    <div class="wdip-menu">
        <button class="show-graph" title="Click to expand">Show Graph</button>
    </div>
    <div class="wdip-data">
        <form>
            <div class="wdip-field wdip-data-amount">
                <label>Base Investment:</label>
                <input type="text" name="amount" placeholder="0.00">
            </div>
            <div class="wdip-field wdip-data-date">
                <label>Choose:</label>
                <input type="text" name="start" placeholder="YYYY-MM-DD">
            </div>
            <div class="wdip-field wdip-data-fee">
                <label>Performance fee:</label>
                <select name="fee">
                    <option value="">Select Performance fee</option>
                </select>
            </div>
            <div class="wdip-field wdip-data-submit">
                <input type="submit" value="Calculate">
            </div>
        </form>
    </div>
</div>
<script>
    jQuery(document).ready(function ($) {
        $('#<?php echo $code; ?>').FXCalculator({
            fee: [<?php echo $attr['fee']; ?>],
            accID: '<?php echo $attr['id']; ?>',
            url: '<?php echo $admin_url; ?>',
            chart_options: JSON.parse('<?php echo $chart_options; ?>')
        });
    });
</script>