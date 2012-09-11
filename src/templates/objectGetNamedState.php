
/**
 * @return string|null
 */
public function getNamedState()
{
    switch ($this->getState()) {
<?php foreach ($states as $state => $name) : ?>
        case self::<?php echo $state ?>:
            return self::<?php echo $name; ?>;

<?php endforeach; ?>
        default:
    }

    return null;
}
