<?php

class StateMachineBehaviorObjectBuilderModifier
{
    /**
     * @var StateMachineBehavior
     */
    private $behavior;

    public function __construct(Behavior $behavior)
    {
        $this->behavior = $behavior;
    }

    public function preInsert($builder)
    {
    }

    public function objectAttributes($builder)
    {
        return $this->behavior->renderTemplate('objectConstants', array(
            'states'            => $this->behavior->getStates(),
        ));
    }

    public function objectMethods($builder)
    {
        $script  = '';

        if (StateMachineBehavior::DEFAULT_STATE_COLUMN !== $this->behavior->getParameter('state_column')) {
            $script .= $this->addGetState($builder);
        }

        $script .= $this->addGetAvailableStates($builder);
        $script .= $this->addGetHumanizedState($builder);
        $script .= $this->addHooks($builder);
        $script .= $this->addIssers($builder);

        return $script;
    }

    public function addGetState($builder)
    {
        return $this->behavior->renderTemplate('objectGetAvailableStates', array(
            'stateColumnGetter' => $this->getColumnGetter('state_column'),
        ));
    }

    public function addGetAvailableStates($builder)
    {
        return $this->behavior->renderTemplate('objectGetAvailableStates', array(
            'states'            => $this->behavior->getStates(),
        ));
    }

    public function addGetHumanizedState($builder)
    {
        return $this->behavior->renderTemplate('objectHumanizedState', array(
            'humanizedStates'   => $this->behavior->getHumanizedStates(),
        ));
    }

    public function addHooks($builder)
    {
        return $this->behavior->renderTemplate('objectHooks', array(
            'states'            => $this->behavior->getStates(),
        ));
    }

    public function addIssers($builder)
    {
        $issers = array();
        foreach ($this->behavior->getStates() as $state) {
            $issers[] = array(
                'methodName'    => $this->getStateIsser($state),
                'constantName'  => $this->getStateConstant($state),
            );
        }

        return $this->behavior->renderTemplate('objectHooks', array(
            'issers'            => $issers,
            'stateColumnGetter' => $this->getColumnGetter('state_column'),
        ));
    }

    public function addSymbolMethods($builder)
    {
        $script = '';
        foreach ($this->behavior->getSymbols() as $symbol) {
            $script .= $this->behavior->renderTemplate('objectSymbolMethod', array(
                'methodName'        => strtolower($symbol),
                'canMethodName'     => $this->getStateCanner($symbol),
                'exceptionClass'    => $this->behavior->getExceptionClass(),
                'modelName'         => '',
                'state'             => '',
            ));
        }

        return $script;
    }

    protected function getColumnSetter($columnName)
    {
        return 'set' . $this->behavior->getColumnForParameter($columnName)->getPhpName();
    }

    protected function getColumnGetter($columnName)
    {
        return 'get' . $this->behavior->getColumnForParameter($columnName)->getPhpName();
    }

    protected function getStateIsser($state)
    {
        return 'is' . ucfirst($state);
    }

    protected function getStateCanner($state)
    {
        return 'can' . ucfirst($state);
    }

    protected function getStateConstant($state)
    {
        return 'self::STATE_' . strtolower($state);
    }

    protected function getModelName($builder)
    {
        return strtolower($this->getObjectClassName($builder));
    }

    protected function getObjectClassName($builder)
    {
        return $builder->getObjectClassname();
    }
}
