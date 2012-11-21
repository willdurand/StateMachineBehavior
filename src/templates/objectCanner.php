
/**
 * @return Boolean
 */
public function <?php echo $methodName  ?>()
{
    $antecedents = array(<?php echo implode($antecedents, ', ') ?>);

    if (static::<?php echo $state ?> === $this->getState() || !in_array($this->getState(), $antecedents)) {
        return false;
    }

    return true;
}
