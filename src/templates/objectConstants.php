<?php foreach ($states as $i => $state) : ?>
/**
 * This constant represents the '<?php echo $state ?>' state.
 */
const STATE_<?php echo strtoupper($state) ?> = <?php echo $i ?>;

<?php endforeach; ?>
