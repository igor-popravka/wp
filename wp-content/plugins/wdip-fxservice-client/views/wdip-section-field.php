<?php
/** @var \WDIP\Plugin\Viewer $this */
/** @var \WDIP\Plugin\ObjectData $options */

$options = $this->getOptions();
?>
<input id="<?= esc_attr($options->label_for); ?>" type="<?= esc_attr($options->type); ?>"
       name="<?= sprintf('%s[%s]', $options->options_name, esc_attr($options->label_for)); ?>"
       value="<?= $options->value; ?>"
<div>
    <p class="description">
        <?= nl2br(__($options->description, $options->options_page)); ?>
    </p>
</div>
