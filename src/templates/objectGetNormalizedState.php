
/**
 * @return string|null
 */
public function getNormalizedState()
{
    switch ($this->getState()) {
<?php foreach ($states as $state => $name) : ?>
        case <?php echo $objectClassName ?>::<?php echo $state ?>:
            return <?php echo $objectClassName ?>::<?php echo $name; ?>;

<?php endforeach; ?>
        default:
    }

    return null;
}

/**
 * @return array
 */
public static function getNormalizedStates()
{
    return array(
<?php foreach ($states as $state => $name) : ?>
    <?php echo $objectClassName ?>::<?php echo $name ?>,
<?php endforeach; ?>
    );
}
