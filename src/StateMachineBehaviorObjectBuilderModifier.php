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

    public function postSave($builder)
    {
        return $this->addPostHooks($builder);
    }

    public function objectMethods($builder)
    {
        $builder->declareClass($this->behavior->getExceptionClass());

        $script  = '';
        if (StateMachineBehavior::DEFAULT_STATE_COLUMN !== $this->behavior->getParameter('state_column')) {
            $script .= $this->addGetState($builder);
        }

        $script .= $this->addGetAvailableStates($builder);
        $script .= $this->addGetHumanizedState($builder);
        $script .= $this->addGetNormalizedState($builder);
        $script .= $this->addHooks($builder);
        $script .= $this->addIssers($builder);
        $script .= $this->addCanners($builder);
        $script .= $this->addSymbolMethods($builder);

        return $script;
    }

    public function addGetState($builder)
    {
        return $this->behavior->renderTemplate('objectGetState', array(
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
        $array = array();
        foreach ($this->behavior->getHumanizedStates() as $state => $humanizedState) {
            $array[$this->getStateConstant($state)] = $humanizedState;
        }

        return $this->behavior->renderTemplate('objectHumanizedState', array(
            'humanizedStates'   => $array,
            'stateColumnGetter' => $this->getColumnGetter('state_column'),
        ));
    }

    public function addGetNormalizedState($builder)
    {
        $map = array();
        foreach ($this->behavior->getStates() as $state) {
            $map[$this->getStateConstant($state)] = $this->getStateNormalizedConstant($state);
        }

        return $this->behavior->renderTemplate('objectGetNormalizedState', array(
            'states'   => $map,
        ));
    }

    public function addHooks($builder)
    {
        $symbols = array();
        foreach ($this->behavior->getSymbols() as $symbol) {
            $symbols[] = array(
                'pre'   => $this->getSymbolPreHook($symbol),
                'on'    => $this->getSymbolOnHook($symbol),
                'post'  => $this->getSymbolPostHook($symbol),
            );
        }

        return $this->behavior->renderTemplate('objectHooks', array(
            'symbols'   => $symbols,
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

        return $this->behavior->renderTemplate('objectIssers', array(
            'issers'            => $issers,
            'stateColumnGetter' => $this->getColumnGetter('state_column'),
        ));
    }

    public function addCanners($builder)
    {
        $script = '';
        foreach ($this->behavior->getSymbols() as $symbol) {
            $antecedentConstants = array();
            foreach ($this->behavior->getAntecedentStates($symbol) as $antecedent) {
                $antecedentConstants[] = 'static::' . $this->getStateConstant($antecedent);
            }

            $script .= $this->behavior->renderTemplate('objectCanner', array(
                'methodName'        => $this->getSymbolCanner($symbol),
                'antecedents'       => $antecedentConstants,
                'state'             => $this->getStateConstant($this->behavior->getStateForSymbol($symbol)),
            ));
        }

        return $script;
    }

    public function addSymbolMethods($builder)
    {
        $script = '';
        foreach ($this->behavior->getSymbols() as $symbol) {
            $script .= $this->behavior->renderTemplate('objectSymbolMethod', array(
                'methodName'            => lcfirst($this->behavior->camelize($symbol)),
                'canMethodName'         => $this->getSymbolCanner($symbol),
                'exceptionClass'        => $this->behavior->getExceptionClass(),
                'modelName'             => $this->getModelName($builder),
                'stateName'             => $this->behavior->humanize($this->behavior->getStateForSymbol($symbol)),
                'preHookMethodName'     => $this->getSymbolPreHook($symbol),
                'onHookMethodName'      => $this->getSymbolOnHook($symbol),
                'postHookMethodName'    => $this->getSymbolPostHook($symbol),
                'stateConstant'         => $this->getStateConstant($this->behavior->getStateForSymbol($symbol)),
                'stateColumnSetter'     => $this->getColumnSetter('state_column'),
            ));
        }

        return $script;
    }

    public function addPostHooks($builder)
    {
        $states = array();
        foreach ($this->behavior->getStates() as $state) {
            if (null !== $symbol = $this->behavior->getSymbolForState($state)) {
                $states[$this->getStateConstant($state)] = $this->getSymbolPostHook($symbol);
            }
        }

        return $this->behavior->renderTemplate('objectPostHooks', array(
            'stateColumnGetter'     => $this->getColumnGetter('state_column'),
            'states'                => $states,
        ));
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
        return 'is' . $this->behavior->camelize($state);
    }

    protected function getSymbolCanner($symbol)
    {
        return 'can' . $this->behavior->camelize($symbol);
    }

    protected function getSymbolPreHook($symbol)
    {
        return 'pre' . $this->behavior->camelize($symbol);
    }

    protected function getSymbolOnHook($symbol)
    {
        return 'on' . $this->behavior->camelize($symbol);
    }

    protected function getSymbolPostHook($symbol)
    {
        return 'post' . $this->behavior->camelize($symbol);
    }

    protected function getStateConstant($state)
    {
        return 'STATE_' . strtoupper($state);
    }

    protected function getStateNormalizedConstant($state)
    {
        return 'STATE_NORMALIZED_' . strtoupper($state);
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
