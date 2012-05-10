
/**
 * @return array
 */
public function getAvailableStates()
{
    return array(
<?php foreach ($states as $state) : ?>
        self::STATE_<?php echo strtoupper($state) ?>,
<?php endforeach; ?>
    );
}
