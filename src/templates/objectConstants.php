<?php foreach ($states as $i => $state) : ?>
/**
 * This constant represents the actual database value for the '<?php echo $state ?>' state.
 *
 * @var int
 */
const STATE_<?php echo strtoupper($state) ?> = <?php echo $i ?>;

/**
 * This constant represents the named state for the '<?php echo $state ?>' state.
 *
 * @var string
 */
const STATE_NORMALIZED_<?php echo strtoupper($state) ?> = "<?php echo $state ?>";

<?php endforeach; ?>
