
/**
 * @return string|null
 */
public function getHumanizedState()
{
    switch ($this-><?php echo $stateColumnGetter ?>()) {
<?php foreach ($humanizedStates as $state => $humanizedState) : ?>
        case <?php echo $objectClassName ?>::<?php echo $state ?>:
            return <?php echo var_export($humanizedState, true) ?>;

<?php endforeach; ?>
        default:
    }

    return null;
}

/**
 * @return array
 */
public static function getHumanizedStates()
{
    return array(
<?php foreach ($humanizedStates as $state => $humanizedState) : ?>
        <?php echo $objectClassName ?>::<?php echo $state ?> => <?php echo var_export($humanizedState, true) ?>,
<?php endforeach; ?>
    );
}
