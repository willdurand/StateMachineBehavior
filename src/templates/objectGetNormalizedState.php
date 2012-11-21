
/**
 * @return string|null
 */
public function getNormalizedState()
{
    switch ($this->getState()) {
<?php foreach ($states as $state => $name) : ?>
        case static::<?php echo $state ?>:
            return static::<?php echo $name; ?>;

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
        static::<?php echo $name ?>,
<?php endforeach; ?>
    );
}
