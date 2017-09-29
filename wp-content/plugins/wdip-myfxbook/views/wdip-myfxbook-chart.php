<?php
/** @var \WDIP\Plugin\Viewer $this */
/** @var \WDIP\Plugin\Options $options */

$options = $this->getOptions();
?>

<div id="<?= $options->uid; ?>" class="wdip-myfxbook-chart"></div>
<script>
    (function ($) {
        $('#<?= $options->uid; ?>').myFxBook(JSON.parse('<?= $options->toJSON(); ?>'));
    })(jQuery);
</script>