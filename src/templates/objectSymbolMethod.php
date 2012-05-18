
/**
 */
public function <?php echo $methodName ?>(PropelPDO $con = null)
{
    if (false === $this-><?php echo $canMethodName ?>()) {
        throw new <?php echo $exceptionClass ?>(sprintf(
            'This <?php echo $modelName ?> object cannot request the <?php echo $stateName ?> state as its current status is %s.',
            $this->getHumanizedState()
        ));
    }

    if ($this-><?php echo $preHookMethodName ?>($con)) {
        $this-><?php echo $onHookMethodName ?>($con);

        $this-><?php echo $stateColumnSetter ?>(self::<?php echo $stateConstant ?>);
    }

    return $this;
}
